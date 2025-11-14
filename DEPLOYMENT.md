# Vendra - DigitalOcean Kubernetes Deployment

## Quick Start

Deploy your Vendra application to DigitalOcean Kubernetes in just a few steps:

### 1. Prerequisites

Install required tools:
```bash
# macOS
brew install doctl kubectl docker

# Other platforms
# Visit: https://docs.digitalocean.com/reference/doctl/how-to/install/
# Visit: https://kubernetes.io/docs/tasks/tools/
```

### 2. Prepare for Deployment

1. **Update secrets** in [k8s/secret.yaml](k8s/secret.yaml):
   ```bash
   # Generate APP_KEY
   php artisan key:generate --show

   # Update these values:
   - APP_KEY: base64:YOUR_GENERATED_KEY
   - APP_URL: https://your-domain.com
   - DB_PASSWORD: your-secure-password
   - MYSQL_ROOT_PASSWORD: your-secure-root-password
   ```

2. **Create a DigitalOcean Container Registry**:
   - Go to https://cloud.digitalocean.com/registry
   - Create a new registry (or note your existing registry name)

### 3. Deploy

Run the automated deployment script:
```bash
./deploy-k8s.sh
```

The script will:
- Authenticate with DigitalOcean
- Configure kubectl for your cluster
- Build and push Docker images
- Deploy all services to Kubernetes
- Run database migrations
- Show your application URL

### 4. Access Your Application

After deployment completes, get your LoadBalancer IP:
```bash
kubectl get svc nginx-service -n vendra
```

Your application will be available at: `http://YOUR_LOADBALANCER_IP`

## What Gets Deployed

| Service | Replicas | Purpose |
|---------|----------|---------|
| PHP-FPM App | 2 | Laravel application |
| Queue Worker | 1 | Background job processing |
| Nginx | 2 | Web server |
| MySQL | 1 | Database (10Gi storage) |
| Redis | 1 | Cache & sessions (5Gi storage) |

## Common Commands

### View Application Status
```bash
kubectl get all -n vendra
```

### View Logs
```bash
# Application logs
kubectl logs -f deployment/vendra-app -n vendra

# Queue worker logs
kubectl logs -f deployment/vendra-queue -n vendra

# Nginx logs
kubectl logs -f deployment/nginx -n vendra
```

### Run Artisan Commands
```bash
# Get app pod name
APP_POD=$(kubectl get pod -n vendra -l app=vendra-app -o jsonpath="{.items[0].metadata.name}")

# Run migrations
kubectl exec -n vendra $APP_POD -- php artisan migrate

# Clear cache
kubectl exec -n vendra $APP_POD -- php artisan cache:clear

# Run tinker
kubectl exec -it -n vendra $APP_POD -- php artisan tinker
```

### Update Application
```bash
# After making code changes and pushing new images
kubectl rollout restart deployment/vendra-app -n vendra
kubectl rollout restart deployment/vendra-queue -n vendra
```

### Scale Application
```bash
# Scale to 3 app instances
kubectl scale deployment/vendra-app -n vendra --replicas=3

# Scale to 2 queue workers
kubectl scale deployment/vendra-queue -n vendra --replicas=2
```

## Next Steps

1. **Set up your domain**:
   - Point your domain's A record to the LoadBalancer IP
   - Update `APP_URL` in [k8s/secret.yaml](k8s/secret.yaml)

2. **Enable SSL/HTTPS**:
   - Install cert-manager: See [k8s/README.md](k8s/README.md#sslhttps-setup)
   - Configure Ingress with your domain

3. **Set up monitoring**:
   - Enable DigitalOcean monitoring
   - Set up alerts for pod failures, high resource usage, etc.

4. **Configure backups**:
   - Set up automated database backups
   - Test restore procedures

## Troubleshooting

### Pods not starting
```bash
# Check pod status
kubectl get pods -n vendra

# Describe pod to see events
kubectl describe pod <pod-name> -n vendra

# View logs
kubectl logs <pod-name> -n vendra
```

### Database connection issues
```bash
# Check MySQL pod
kubectl get pods -n vendra -l app=mysql

# Test connection
kubectl exec -it deployment/vendra-app -n vendra -- php artisan tinker
# Then: DB::connection()->getPdo();
```

### Cannot pull images
```bash
# Ensure registry is integrated with cluster
doctl kubernetes cluster registry add a311b22b-7974-474a-b872-62be85dfc5ce

# Verify images exist
doctl registry repository list-v2
```

## Cost Information

Estimated monthly costs (DigitalOcean):
- Kubernetes cluster: $12-$120 (depending on node size/count)
- Block Storage (PVCs): ~$1/10GB ($1.50 total)
- Container Registry: $5 (basic tier)
- LoadBalancer: $12

**Total: ~$30-$140/month** (depending on cluster size)

## Architecture Diagram

```
                                    Internet
                                       |
                                       v
                               [LoadBalancer]
                                       |
                                       v
                                   [Nginx x2]
                                       |
                                       v
                                 [PHP-FPM x2] <---> [Queue x1]
                                       |              |
                                       +------+-------+
                                              |
                                       +------+-------+
                                       |              |
                                       v              v
                                   [MySQL]        [Redis]
                                   (10Gi PVC)     (5Gi PVC)
```

## Security Checklist

- [ ] Update all secrets in `k8s/secret.yaml`
- [ ] Use strong database passwords
- [ ] Enable SSL/HTTPS with cert-manager
- [ ] Restrict database access to cluster only
- [ ] Enable DigitalOcean firewall rules
- [ ] Regularly update Docker images
- [ ] Enable pod security policies
- [ ] Set up network policies
- [ ] Enable audit logging
- [ ] Regular security scans of images

## Support & Documentation

- Full Kubernetes guide: [k8s/README.md](k8s/README.md)
- DigitalOcean Kubernetes: https://docs.digitalocean.com/products/kubernetes/
- Laravel Deployment: https://laravel.com/docs/deployment

## Cluster Information

- **Cluster ID**: `a311b22b-7974-474a-b872-62be85dfc5ce`
- **Namespace**: `vendra`
- **Region**: Check with `doctl kubernetes cluster get a311b22b-7974-474a-b872-62be85dfc5ce`

---

**Ready to deploy?** Run `./deploy-k8s.sh` to get started!
