# Kubernetes Deployment Guide for Vendra

This guide will help you deploy the Vendra Laravel application to your DigitalOcean Kubernetes cluster.

## Prerequisites

Before deploying, ensure you have the following installed:

1. **doctl** (DigitalOcean CLI)
   ```bash
   brew install doctl
   # Or visit: https://docs.digitalocean.com/reference/doctl/how-to/install/
   ```

2. **kubectl** (Kubernetes CLI)
   ```bash
   brew install kubectl
   ```

3. **Docker**
   - Visit: https://docs.docker.com/get-docker/

## Quick Start

### Automated Deployment

The easiest way to deploy is using the automated script:

```bash
./deploy-k8s.sh
```

This script will:
1. Check prerequisites
2. Authenticate with DigitalOcean
3. Configure kubectl for your cluster
4. Build Docker images
5. Push images to DigitalOcean Container Registry
6. Deploy all services to Kubernetes
7. Run database migrations
8. Display your application URL

### Manual Deployment

If you prefer to deploy manually, follow these steps:

#### 1. Authenticate with DigitalOcean

```bash
doctl auth init
```

#### 2. Configure kubectl

```bash
doctl kubernetes cluster kubeconfig save a311b22b-7974-474a-b872-62be85dfc5ce
```

#### 3. Set up Container Registry

```bash
# Create a registry (if you don't have one)
doctl registry create your-registry-name --subscription-tier basic

# Login to the registry
doctl registry login
```

#### 4. Update Configuration

Update the following files with your values:

**k8s/secret.yaml:**
```yaml
APP_KEY: "base64:YOUR_GENERATED_KEY"  # Generate with: php artisan key:generate --show
APP_URL: "https://your-domain.com"
DB_PASSWORD: "your-secure-password"
MYSQL_ROOT_PASSWORD: "your-secure-root-password"
```

**All k8s/*.yaml files:**
Replace `YOUR_REGISTRY_NAME` with your actual DigitalOcean registry name.

#### 5. Build and Push Docker Images

```bash
# Set your registry name
REGISTRY_NAME="your-registry-name"

# Build images
docker build -f docker/php/Dockerfile -t registry.digitalocean.com/$REGISTRY_NAME/vendra-app:latest .
docker build -f docker/queue/Dockerfile -t registry.digitalocean.com/$REGISTRY_NAME/vendra-queue:latest .
docker build -f docker/nginx/Dockerfile -t registry.digitalocean.com/$REGISTRY_NAME/vendra-nginx:latest .

# Push images
docker push registry.digitalocean.com/$REGISTRY_NAME/vendra-app:latest
docker push registry.digitalocean.com/$REGISTRY_NAME/vendra-queue:latest
docker push registry.digitalocean.com/$REGISTRY_NAME/vendra-nginx:latest
```

#### 6. Deploy to Kubernetes

```bash
# Apply manifests in order
kubectl apply -f k8s/namespace.yaml
kubectl apply -f k8s/configmap.yaml
kubectl apply -f k8s/secret.yaml
kubectl apply -f k8s/mysql.yaml
kubectl apply -f k8s/redis.yaml

# Wait for database to be ready
kubectl wait --for=condition=ready pod -l app=mysql -n vendra --timeout=300s

# Deploy application services
kubectl apply -f k8s/app.yaml
kubectl apply -f k8s/queue.yaml
kubectl apply -f k8s/nginx.yaml
```

#### 7. Run Database Migrations

```bash
APP_POD=$(kubectl get pod -n vendra -l app=vendra-app -o jsonpath="{.items[0].metadata.name}")
kubectl exec -n vendra $APP_POD -- php artisan migrate --force
```

#### 8. Get LoadBalancer IP

```bash
kubectl get svc nginx-service -n vendra
```

## Architecture

The deployment consists of the following components:

- **Namespace**: `vendra` - Isolated namespace for all resources
- **PHP-FPM App**: 2 replicas for high availability
- **Queue Workers**: 1 replica for processing background jobs
- **Nginx**: 2 replicas serving as web server
- **MySQL**: 1 replica with persistent storage (10Gi)
- **Redis**: 1 replica for caching and sessions (5Gi)
- **LoadBalancer**: External access to the application

## Kubernetes Resources

### ConfigMap
- Environment variables for the application
- Located in: `k8s/configmap.yaml`

### Secret
- Sensitive data (API keys, passwords)
- Located in: `k8s/secret.yaml`
- **Important**: Update with production values before deploying

### PersistentVolumeClaims
- **mysql-pvc**: 10Gi for MySQL data
- **redis-pvc**: 5Gi for Redis data
- Uses DigitalOcean Block Storage

### Services
- **app-service**: ClusterIP for PHP-FPM (port 9000)
- **mysql-service**: ClusterIP for MySQL (port 3306)
- **redis-service**: ClusterIP for Redis (port 6379)
- **nginx-service**: LoadBalancer for external access (port 80)

## Useful Commands

### View Resources
```bash
# All resources in the namespace
kubectl get all -n vendra

# Pods
kubectl get pods -n vendra

# Services
kubectl get svc -n vendra

# Deployments
kubectl get deployments -n vendra
```

### View Logs
```bash
# App logs
kubectl logs -f deployment/vendra-app -n vendra

# Nginx logs
kubectl logs -f deployment/nginx -n vendra

# Queue worker logs
kubectl logs -f deployment/vendra-queue -n vendra

# MySQL logs
kubectl logs -f deployment/mysql -n vendra
```

### Execute Commands
```bash
# Get a shell in the app container
kubectl exec -it deployment/vendra-app -n vendra -- bash

# Run artisan commands
kubectl exec -n vendra deployment/vendra-app -- php artisan migrate
kubectl exec -n vendra deployment/vendra-app -- php artisan config:cache
kubectl exec -n vendra deployment/vendra-app -- php artisan route:cache
```

### Scaling
```bash
# Scale app replicas
kubectl scale deployment/vendra-app -n vendra --replicas=3

# Scale queue workers
kubectl scale deployment/vendra-queue -n vendra --replicas=2
```

### Update Application
```bash
# After pushing new images
kubectl rollout restart deployment/vendra-app -n vendra
kubectl rollout restart deployment/vendra-queue -n vendra
kubectl rollout restart deployment/nginx -n vendra

# Check rollout status
kubectl rollout status deployment/vendra-app -n vendra
```

### Database Operations
```bash
# Access MySQL
kubectl exec -it deployment/mysql -n vendra -- mysql -u root -p

# Run migrations
APP_POD=$(kubectl get pod -n vendra -l app=vendra-app -o jsonpath="{.items[0].metadata.name}")
kubectl exec -n vendra $APP_POD -- php artisan migrate

# Seed database
kubectl exec -n vendra $APP_POD -- php artisan db:seed
```

## SSL/HTTPS Setup

For production, you should set up SSL using cert-manager and Let's Encrypt:

### 1. Install cert-manager
```bash
kubectl apply -f https://github.com/cert-manager/cert-manager/releases/download/v1.13.0/cert-manager.yaml
```

### 2. Create ClusterIssuer
```yaml
apiVersion: cert-manager.io/v1
kind: ClusterIssuer
metadata:
  name: letsencrypt-prod
spec:
  acme:
    server: https://acme-v02.api.letsencrypt.org/directory
    email: your-email@example.com
    privateKeySecretRef:
      name: letsencrypt-prod
    solvers:
    - http01:
        ingress:
          class: nginx
```

### 3. Update Ingress
Edit `k8s/ingress.yaml` with your domain and apply:
```bash
kubectl apply -f k8s/ingress.yaml
```

## Monitoring and Maintenance

### Health Checks
```bash
# Check if all pods are running
kubectl get pods -n vendra

# Check if all deployments are available
kubectl get deployments -n vendra
```

### Backup Database
```bash
# Backup MySQL
kubectl exec deployment/mysql -n vendra -- mysqldump -u root -p vendra > backup.sql

# Restore MySQL
kubectl exec -i deployment/mysql -n vendra -- mysql -u root -p vendra < backup.sql
```

### Resource Usage
```bash
# Check resource usage
kubectl top pods -n vendra
kubectl top nodes
```

## Troubleshooting

### Pods not starting
```bash
# Describe pod to see events
kubectl describe pod <pod-name> -n vendra

# Check logs
kubectl logs <pod-name> -n vendra
```

### Database connection issues
```bash
# Check if MySQL is running
kubectl get pods -n vendra -l app=mysql

# Test connection from app pod
kubectl exec -it deployment/vendra-app -n vendra -- php artisan tinker
# Then run: DB::connection()->getPdo();
```

### LoadBalancer not getting IP
```bash
# Check service status
kubectl describe svc nginx-service -n vendra

# Check cluster events
kubectl get events -n vendra --sort-by='.lastTimestamp'
```

### Image pull errors
```bash
# Ensure registry integration is set up
doctl kubernetes cluster registry add a311b22b-7974-474a-b872-62be85dfc5ce

# Verify images are in registry
doctl registry repository list-v2
```

## Cost Optimization

1. **Right-size resources**: Adjust resource requests/limits based on actual usage
2. **Use node auto-scaling**: Enable cluster autoscaler
3. **Optimize storage**: Use appropriate storage tiers
4. **Monitor unused resources**: Regularly check for unused PVCs and LoadBalancers

## Security Best Practices

1. **Secrets Management**: Never commit secrets to git
2. **Network Policies**: Restrict pod-to-pod communication
3. **RBAC**: Use role-based access control
4. **Image Scanning**: Scan images for vulnerabilities
5. **Updates**: Keep cluster and applications updated

## Support

For issues or questions:
- DigitalOcean Kubernetes: https://docs.digitalocean.com/products/kubernetes/
- Kubernetes Documentation: https://kubernetes.io/docs/
- Laravel Deployment: https://laravel.com/docs/deployment

## License

This deployment configuration is part of the Vendra project.
