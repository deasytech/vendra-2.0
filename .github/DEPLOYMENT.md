# GitHub Actions CI/CD Setup

This repository uses GitHub Actions for automated deployment to DigitalOcean Kubernetes.

## Required GitHub Secrets

To enable automated deployments, you need to configure the following secrets in your GitHub repository:

### Settings → Secrets and variables → Actions → New repository secret

1. **DIGITALOCEAN_ACCESS_TOKEN**
   - Your DigitalOcean API token
   - Get it from: https://cloud.digitalocean.com/account/api/tokens
   - Permissions needed: Read/Write for Container Registry and Kubernetes

2. **CLUSTER_ID**
   - Your Kubernetes cluster ID
   - Current cluster ID: `a311b22b-7974-474a-b872-62be85dfc5ce`
   - Find it with: `doctl kubernetes cluster list`

## How to Add Secrets

1. Go to your GitHub repository
2. Click on **Settings** tab
3. Navigate to **Secrets and variables** → **Actions**
4. Click **New repository secret**
5. Add each secret with its name and value
6. Click **Add secret**

## Workflow Trigger

The deployment workflow automatically runs when you push to the `main` branch:

```bash
git add .
git commit -m "Your commit message"
git push origin main
```

## Workflow Steps

The GitHub Actions workflow performs the following steps:

1. **Checkout code** - Pulls the latest code from the repository
2. **Setup Docker Buildx** - Prepares Docker for multi-platform builds
3. **Install doctl** - Installs DigitalOcean CLI
4. **Login to Registry** - Authenticates with DO Container Registry
5. **Build & Push Images** - Builds and pushes Docker images for:
   - `vendra-app` (PHP-FPM application)
   - `vendra-queue` (Queue worker)
6. **Setup kubectl** - Configures Kubernetes CLI
7. **Deploy to K8s** - Applies all Kubernetes manifests
8. **Run Migrations** - Executes database migrations
9. **Verify Deployment** - Checks pod and service status

## Manual Deployment

If you need to deploy manually:

```bash
# Build and push images
docker buildx build --platform linux/amd64 \
  -f docker/php/Dockerfile \
  -t registry.digitalocean.com/vendra-registry/vendra-app:latest \
  --push .

docker buildx build --platform linux/amd64 \
  -f docker/queue/Dockerfile \
  -t registry.digitalocean.com/vendra-registry/vendra-queue:latest \
  --push .

# Deploy to Kubernetes
kubectl apply -f k8s/
kubectl rollout restart deployment/vendra-app -n vendra
kubectl rollout restart deployment/vendra-queue -n vendra
```

## Monitoring Deployments

View deployment status:
- GitHub Actions: https://github.com/YOUR_USERNAME/YOUR_REPO/actions
- Application: https://einv.palbillr.com
- Admin Panel: https://einv.palbillr.com/admin/login

Check pod status:
```bash
kubectl get pods -n vendra
kubectl logs -n vendra -l app=vendra-app -c app --tail=50
```

## Rollback

To rollback to a previous deployment:

```bash
# View deployment history
kubectl rollout history deployment/vendra-app -n vendra

# Rollback to previous version
kubectl rollout undo deployment/vendra-app -n vendra
kubectl rollout undo deployment/vendra-queue -n vendra
```

## Troubleshooting

### Build fails with "Insufficient CPU"
The cluster might not have enough resources. Either:
- Scale down replicas temporarily
- Delete old pods before new ones start
- Upgrade cluster nodes

### Image pull errors
Ensure the cluster is connected to the container registry:
```bash
doctl kubernetes cluster registry add a311b22b-7974-474a-b872-62be85dfc5ce
```

### Migration failures
Migrations run with `--force` flag in production. Check logs:
```bash
kubectl logs -n vendra -l app=vendra-app -c app | grep migrate
```

## Environment Configuration

Update environment variables in [k8s/secret.yaml](../k8s/secret.yaml), then:
```bash
kubectl apply -f k8s/secret.yaml
kubectl rollout restart deployment/vendra-app -n vendra
```
