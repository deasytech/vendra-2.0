#!/bin/bash

# Kubernetes Helper Commands for Vendra
# This script provides convenient shortcuts for common k8s operations

NAMESPACE="vendra"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to get app pod name
get_app_pod() {
    kubectl get pod -n $NAMESPACE -l app=vendra-app -o jsonpath="{.items[0].metadata.name}"
}

# Display menu
show_menu() {
    clear
    echo -e "${BLUE}╔════════════════════════════════════════╗${NC}"
    echo -e "${BLUE}║    Vendra Kubernetes Helper Menu      ║${NC}"
    echo -e "${BLUE}╚════════════════════════════════════════╝${NC}"
    echo ""
    echo "📊 Status & Information:"
    echo "  1)  View all resources"
    echo "  2)  View pods status"
    echo "  3)  View services"
    echo "  4)  Get LoadBalancer IP"
    echo "  5)  View resource usage"
    echo ""
    echo "📝 Logs:"
    echo "  6)  App logs (follow)"
    echo "  7)  Queue logs (follow)"
    echo "  8)  Nginx logs (follow)"
    echo "  9)  MySQL logs (follow)"
    echo ""
    echo "🔧 Operations:"
    echo "  10) Run migrations"
    echo "  11) Clear cache"
    echo "  12) Run artisan command"
    echo "  13) Open shell in app pod"
    echo "  14) Run tinker"
    echo ""
    echo "🔄 Deployment:"
    echo "  15) Restart app deployment"
    echo "  16) Restart queue deployment"
    echo "  17) Restart all deployments"
    echo "  18) Scale app replicas"
    echo ""
    echo "🗄️  Database:"
    echo "  19) Access MySQL shell"
    echo "  20) Backup database"
    echo "  21) Test database connection"
    echo ""
    echo "❌ 0) Exit"
    echo ""
    echo -n "Select an option: "
}

# Function implementations
view_all() {
    echo -e "${GREEN}All resources in namespace $NAMESPACE:${NC}"
    kubectl get all -n $NAMESPACE
}

view_pods() {
    echo -e "${GREEN}Pods in namespace $NAMESPACE:${NC}"
    kubectl get pods -n $NAMESPACE -o wide
}

view_services() {
    echo -e "${GREEN}Services in namespace $NAMESPACE:${NC}"
    kubectl get svc -n $NAMESPACE
}

get_lb_ip() {
    echo -e "${GREEN}LoadBalancer IP:${NC}"
    LB_IP=$(kubectl get svc nginx-service -n $NAMESPACE -o jsonpath='{.status.loadBalancer.ingress[0].ip}')
    if [ -z "$LB_IP" ]; then
        echo -e "${YELLOW}LoadBalancer IP not yet assigned${NC}"
    else
        echo -e "${GREEN}http://$LB_IP${NC}"
    fi
}

view_usage() {
    echo -e "${GREEN}Resource usage:${NC}"
    kubectl top pods -n $NAMESPACE
}

view_app_logs() {
    echo -e "${GREEN}Following app logs (Ctrl+C to exit):${NC}"
    kubectl logs -f deployment/vendra-app -n $NAMESPACE
}

view_queue_logs() {
    echo -e "${GREEN}Following queue logs (Ctrl+C to exit):${NC}"
    kubectl logs -f deployment/vendra-queue -n $NAMESPACE
}

view_nginx_logs() {
    echo -e "${GREEN}Following nginx logs (Ctrl+C to exit):${NC}"
    kubectl logs -f deployment/nginx -n $NAMESPACE
}

view_mysql_logs() {
    echo -e "${GREEN}Following MySQL logs (Ctrl+C to exit):${NC}"
    kubectl logs -f deployment/mysql -n $NAMESPACE
}

run_migrations() {
    echo -e "${GREEN}Running migrations...${NC}"
    APP_POD=$(get_app_pod)
    kubectl exec -n $NAMESPACE $APP_POD -- php artisan migrate --force
    echo -e "${GREEN}✅ Migrations completed${NC}"
}

clear_cache() {
    echo -e "${GREEN}Clearing cache...${NC}"
    APP_POD=$(get_app_pod)
    kubectl exec -n $NAMESPACE $APP_POD -- php artisan cache:clear
    kubectl exec -n $NAMESPACE $APP_POD -- php artisan config:clear
    kubectl exec -n $NAMESPACE $APP_POD -- php artisan route:clear
    kubectl exec -n $NAMESPACE $APP_POD -- php artisan view:clear
    echo -e "${GREEN}✅ Cache cleared${NC}"
}

run_artisan() {
    echo -n "Enter artisan command (without 'php artisan'): "
    read COMMAND
    APP_POD=$(get_app_pod)
    kubectl exec -n $NAMESPACE $APP_POD -- php artisan $COMMAND
}

open_shell() {
    echo -e "${GREEN}Opening shell in app pod...${NC}"
    kubectl exec -it deployment/vendra-app -n $NAMESPACE -- bash
}

run_tinker() {
    echo -e "${GREEN}Starting tinker...${NC}"
    APP_POD=$(get_app_pod)
    kubectl exec -it -n $NAMESPACE $APP_POD -- php artisan tinker
}

restart_app() {
    echo -e "${GREEN}Restarting app deployment...${NC}"
    kubectl rollout restart deployment/vendra-app -n $NAMESPACE
    kubectl rollout status deployment/vendra-app -n $NAMESPACE
    echo -e "${GREEN}✅ App restarted${NC}"
}

restart_queue() {
    echo -e "${GREEN}Restarting queue deployment...${NC}"
    kubectl rollout restart deployment/vendra-queue -n $NAMESPACE
    kubectl rollout status deployment/vendra-queue -n $NAMESPACE
    echo -e "${GREEN}✅ Queue restarted${NC}"
}

restart_all() {
    echo -e "${GREEN}Restarting all deployments...${NC}"
    kubectl rollout restart deployment/vendra-app -n $NAMESPACE
    kubectl rollout restart deployment/vendra-queue -n $NAMESPACE
    kubectl rollout restart deployment/nginx -n $NAMESPACE
    echo -e "${GREEN}✅ All deployments restarted${NC}"
}

scale_app() {
    echo -n "Enter number of app replicas: "
    read REPLICAS
    kubectl scale deployment/vendra-app -n $NAMESPACE --replicas=$REPLICAS
    echo -e "${GREEN}✅ App scaled to $REPLICAS replicas${NC}"
}

mysql_shell() {
    echo -e "${GREEN}Opening MySQL shell...${NC}"
    echo -e "${YELLOW}Password is in k8s/secret.yaml (MYSQL_ROOT_PASSWORD)${NC}"
    kubectl exec -it deployment/mysql -n $NAMESPACE -- mysql -u root -p
}

backup_db() {
    BACKUP_FILE="vendra_backup_$(date +%Y%m%d_%H%M%S).sql"
    echo -e "${GREEN}Backing up database to $BACKUP_FILE...${NC}"
    echo -e "${YELLOW}Password is in k8s/secret.yaml (MYSQL_ROOT_PASSWORD)${NC}"
    kubectl exec deployment/mysql -n $NAMESPACE -- mysqldump -u root -p vendra > $BACKUP_FILE
    echo -e "${GREEN}✅ Database backed up to $BACKUP_FILE${NC}"
}

test_db_connection() {
    echo -e "${GREEN}Testing database connection...${NC}"
    APP_POD=$(get_app_pod)
    kubectl exec -n $NAMESPACE $APP_POD -- php -r "require '/var/www/vendor/autoload.php'; \$app = require_once '/var/www/bootstrap/app.php'; \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); try { DB::connection()->getPdo(); echo 'Database connection successful\n'; } catch (\Exception \$e) { echo 'Database connection failed: ' . \$e->getMessage() . '\n'; }"
}

# Main loop
while true; do
    show_menu
    read choice
    echo ""

    case $choice in
        1) view_all ;;
        2) view_pods ;;
        3) view_services ;;
        4) get_lb_ip ;;
        5) view_usage ;;
        6) view_app_logs ;;
        7) view_queue_logs ;;
        8) view_nginx_logs ;;
        9) view_mysql_logs ;;
        10) run_migrations ;;
        11) clear_cache ;;
        12) run_artisan ;;
        13) open_shell ;;
        14) run_tinker ;;
        15) restart_app ;;
        16) restart_queue ;;
        17) restart_all ;;
        18) scale_app ;;
        19) mysql_shell ;;
        20) backup_db ;;
        21) test_db_connection ;;
        0) echo -e "${GREEN}Goodbye!${NC}"; exit 0 ;;
        *) echo -e "${RED}Invalid option${NC}" ;;
    esac

    echo ""
    echo -n "Press Enter to continue..."
    read
done
