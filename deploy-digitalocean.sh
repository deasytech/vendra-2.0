#!/bin/bash

echo "🚀 Vendra Digital Ocean Deployment Script"
echo "=========================================="

# Check if required files exist
if [ ! -f "docker-compose.prod.yml" ]; then
    echo "❌ docker-compose.prod.yml not found!"
    exit 1
fi

if [ ! -f ".env.production" ]; then
    echo "⚠️  .env.production not found. Creating from template..."
    cp .env.docker .env.production
    echo "📝 Please edit .env.production with your production values"
fi

# Generate application key if not set
if grep -q "APP_KEY=base64:your-app-key-here" .env.production; then
    echo "🔑 Generating application key..."
    php artisan key:generate --show > /tmp/app_key.txt
    APP_KEY=$(cat /tmp/app_key.txt)
    sed -i.bak "s|APP_KEY=base64:your-app-key-here|APP_KEY=$APP_KEY|g" .env.production
    rm /tmp/app_key.txt
    echo "✅ Application key generated"
fi

echo ""
echo "📋 Digital Ocean Deployment Instructions:"
echo "=========================================="
echo ""
echo "1. 🌐 Create a Digital Ocean Droplet:"
echo "   - Choose Ubuntu 22.04 LTS"
echo "   - Minimum 2GB RAM recommended"
echo "   - Enable backups"
echo ""
echo "2. 🔧 Install Docker on your droplet:"
echo "   curl -fsSL https://get.docker.com -o get-docker.sh"
echo "   sudo sh get-docker.sh"
echo "   sudo usermod -aG docker \${USER}"
echo ""
echo "3. 📁 Upload your project files to the droplet:"
echo "   - Use SCP, SFTP, or Git clone"
echo "   - Ensure all files are in /var/www/vendra"
echo ""
echo "4. 🔐 Set up environment variables:"
echo "   - Copy .env.production to .env on the server"
echo "   - Update database credentials"
echo "   - Set APP_URL to your domain"
echo ""
echo "5. 🐳 Deploy with Docker Compose:"
echo "   docker-compose -f docker-compose.prod.yml up -d"
echo ""
echo "6. 🗄️ Run migrations:"
echo "   docker-compose -f docker-compose.prod.yml exec app php artisan migrate"
echo ""
echo "7. 🔒 Set up SSL (Let's Encrypt):"
echo "   sudo apt install certbot python3-certbot-nginx"
echo "   sudo certbot --nginx -d yourdomain.com"
echo ""
echo "8. 🔄 Set up automatic updates:"
echo "   - Configure unattended-upgrades"
echo "   - Set up monitoring with Digital Ocean monitoring"
echo ""
echo "9. 📊 Monitoring commands:"
echo "   docker-compose -f docker-compose.prod.yml logs -f"
echo "   docker-compose -f docker-compose.prod.yml ps"
echo ""
echo "🎯 Production Environment Variables:"
echo "====================================="
echo "Required environment variables for production:"
echo "- APP_KEY (generated above)"
echo "- APP_URL (your domain)"
echo "- DB_DATABASE"
echo "- DB_USERNAME"
echo "- DB_PASSWORD"
echo "- DB_ROOT_PASSWORD"
echo ""
echo "✅ Deployment script ready!"
echo "🚀 Your Laravel application is ready for Digital Ocean deployment!"
