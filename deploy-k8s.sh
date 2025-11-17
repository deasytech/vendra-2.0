#!/bin/bash

set -e

echo "🚀 Vendra Kubernetes Deployment Script"
echo "======================================="
echo ""

# Configuration
CLUSTER_ID="a311b22b-7974-474a-b872-62be85dfc5ce"
REGISTRY_NAME="vendra-registry"  # Change this to your DO registry name
PROJECT_NAME="vendra"
APP_VERSION="latest"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check prerequisites
echo "🔍 Checking prerequisites..."

if ! command -v doctl &> /dev/null; then
    echo -e "${RED}❌ doctl is not installed. Please install it first:${NC}"
    echo "   brew install doctl"
    echo "   or visit: https://docs.digitalocean.com/reference/doctl/how-to/install/"
    exit 1
fi

if ! command -v kubectl &> /dev/null; then
    echo -e "${RED}❌ kubectl is not installed. Please install it first:${NC}"
    echo "   brew install kubectl"
    exit 1
fi

if ! command -v docker &> /dev/null; then
    echo -e "${RED}❌ Docker is not installed. Please install it first:${NC}"
    echo "   Visit: https://docs.docker.com/get-docker/"
    exit 1
fi

echo -e "${GREEN}✅ All prerequisites installed${NC}"
echo ""

# Function to update registry name in files
update_registry_name() {
    if [ "$REGISTRY_NAME" = "YOUR_REGISTRY_NAME" ]; then
        echo -e "${YELLOW}⚠️  Please update REGISTRY_NAME in this script${NC}"
        echo -e "${YELLOW}   Get your registry name from: https://cloud.digitalocean.com/registry${NC}"
        echo ""
        read -p "Enter your DigitalOcean registry name: " REGISTRY_NAME
        if [ -z "$REGISTRY_NAME" ]; then
            echo -e "${RED}❌ Registry name cannot be empty${NC}"
            exit 1
        fi
        # Update the script itself
        sed -i.bak "s/REGISTRY_NAME=\"YOUR_REGISTRY_NAME\"/REGISTRY_NAME=\"$REGISTRY_NAME\"/" "$0"
        rm "$0.bak"
    fi

    # Update all k8s manifests
    find k8s/ -name "*.yaml" -type f -exec sed -i.bak "s/YOUR_REGISTRY_NAME/$REGISTRY_NAME/g" {} \;
    find k8s/ -name "*.yaml.bak" -type f -delete

    echo -e "${GREEN}✅ Registry name updated to: $REGISTRY_NAME${NC}"
}

# Step 1: Update registry name
echo "📝 Step 1: Configure registry name"
update_registry_name
echo ""

# Step 2: Authenticate with DigitalOcean
echo "🔐 Step 2: Authenticate with DigitalOcean"
echo "Running: doctl auth init"
if ! doctl account get &> /dev/null; then
    doctl auth init
else
    echo -e "${GREEN}✅ Already authenticated${NC}"
fi
echo ""

# Step 3: Configure kubectl
echo "⚙️  Step 3: Configure kubectl for cluster $CLUSTER_ID"
echo "Running: doctl kubernetes cluster kubeconfig save $CLUSTER_ID"
doctl kubernetes cluster kubeconfig save $CLUSTER_ID
echo -e "${GREEN}✅ kubectl configured${NC}"
echo ""

# Step 4: Set up Container Registry
echo "🐳 Step 4: Set up DigitalOcean Container Registry"
if ! doctl registry get &> /dev/null; then
    echo -e "${YELLOW}⚠️  No registry found. Creating one...${NC}"
    read -p "Enter a name for your registry: " NEW_REGISTRY_NAME
    doctl registry create $NEW_REGISTRY_NAME --subscription-tier basic
    REGISTRY_NAME=$NEW_REGISTRY_NAME
    update_registry_name
else
    echo -e "${GREEN}✅ Registry already exists${NC}"
fi

# Login to registry
echo "Logging into Docker registry..."
doctl registry login
echo -e "${GREEN}✅ Logged into registry${NC}"
echo ""

# Step 5: Build Docker images
echo "🔨 Step 5: Build Docker images"
echo "Building vendra-app..."
docker build -f docker/php/Dockerfile -t registry.digitalocean.com/$REGISTRY_NAME/vendra-app:$APP_VERSION .

echo "Building vendra-queue..."
docker build -f docker/queue/Dockerfile -t registry.digitalocean.com/$REGISTRY_NAME/vendra-queue:$APP_VERSION .

echo "Building vendra-nginx..."
docker build -f docker/nginx/Dockerfile -t registry.digitalocean.com/$REGISTRY_NAME/vendra-nginx:$APP_VERSION .

echo -e "${GREEN}✅ All images built successfully${NC}"
echo ""

# Step 6: Push images to registry
echo "📤 Step 6: Push images to DigitalOcean Container Registry"
docker push registry.digitalocean.com/$REGISTRY_NAME/vendra-app:$APP_VERSION
docker push registry.digitalocean.com/$REGISTRY_NAME/vendra-queue:$APP_VERSION
docker push registry.digitalocean.com/$REGISTRY_NAME/vendra-nginx:$APP_VERSION
echo -e "${GREEN}✅ All images pushed successfully${NC}"
echo ""

# Step 7: Update secrets
echo "🔐 Step 7: Configure secrets"
echo -e "${YELLOW}⚠️  IMPORTANT: Update k8s/secret.yaml with your production values${NC}"
echo "   - APP_KEY (generate with: php artisan key:generate --show)"
echo "   - APP_URL (your production domain)"
echo "   - Database credentials"
echo ""
read -p "Have you updated k8s/secret.yaml? (y/n) " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${RED}❌ Please update k8s/secret.yaml before deploying${NC}"
    exit 1
fi
echo ""

# Step 8: Deploy to Kubernetes
echo "🚀 Step 8: Deploy to Kubernetes"
echo "Applying Kubernetes manifests..."

kubectl apply -f k8s/namespace.yaml
kubectl apply -f k8s/configmap.yaml
kubectl apply -f k8s/secret.yaml
kubectl apply -f k8s/mysql.yaml
kubectl apply -f k8s/redis.yaml

echo "Waiting for MySQL and Redis to be ready..."
kubectl wait --for=condition=ready pod -l app=mysql -n vendra --timeout=300s
kubectl wait --for=condition=ready pod -l app=redis -n vendra --timeout=300s

kubectl apply -f k8s/app.yaml
kubectl apply -f k8s/queue.yaml
kubectl apply -f k8s/nginx.yaml

echo -e "${GREEN}✅ All manifests applied${NC}"
echo ""

# Step 9: Wait for deployments
echo "⏳ Step 9: Wait for deployments to be ready..."
kubectl wait --for=condition=available deployment/vendra-app -n vendra --timeout=300s
kubectl wait --for=condition=available deployment/nginx -n vendra --timeout=300s
echo -e "${GREEN}✅ All deployments ready${NC}"
echo ""

# Step 10: Run migrations
echo "📊 Step 10: Run database migrations"
APP_POD=$(kubectl get pod -n vendra -l app=vendra-app -o jsonpath="{.items[0].metadata.name}")
echo "Running migrations in pod: $APP_POD"
kubectl exec -n vendra $APP_POD -- php artisan migrate --force
echo -e "${GREEN}✅ Migrations completed${NC}"
echo ""

# Step 11: Get service information
echo "🌐 Step 11: Service Information"
echo "================================"
echo ""
echo "Getting LoadBalancer IP..."
sleep 10  # Wait for LoadBalancer to get an IP
EXTERNAL_IP=$(kubectl get svc nginx-service -n vendra -o jsonpath='{.status.loadBalancer.ingress[0].ip}')

if [ -z "$EXTERNAL_IP" ]; then
    echo -e "${YELLOW}⚠️  LoadBalancer IP not yet assigned. Run this command to check:${NC}"
    echo "   kubectl get svc nginx-service -n vendra"
else
    echo -e "${GREEN}✅ Your application is accessible at: http://$EXTERNAL_IP${NC}"
fi
echo ""

# Useful commands
echo "📋 Useful Commands"
echo "=================="
echo "View all resources:      kubectl get all -n vendra"
echo "View logs (app):         kubectl logs -f deployment/vendra-app -n vendra"
echo "View logs (nginx):       kubectl logs -f deployment/nginx -n vendra"
echo "View logs (queue):       kubectl logs -f deployment/vendra-queue -n vendra"
echo "Execute command in app:  kubectl exec -it deployment/vendra-app -n vendra -- bash"
echo "Run migrations:          kubectl exec -n vendra \$(kubectl get pod -n vendra -l app=vendra-app -o jsonpath=\"{.items[0].metadata.name}\") -- php artisan migrate"
echo "Check LoadBalancer IP:   kubectl get svc nginx-service -n vendra"
echo ""

echo -e "${GREEN}🎉 Deployment completed successfully!${NC}"
echo ""
echo "⚠️  Next Steps:"
echo "1. Point your domain to the LoadBalancer IP: $EXTERNAL_IP"
echo "2. Update APP_URL in k8s/secret.yaml to your domain"
echo "3. Consider setting up Ingress with SSL (see k8s/ingress.yaml)"
echo "4. Set up monitoring and logging"
echo ""
