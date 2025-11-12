#!/bin/bash

echo "🚀 Setting up Vendra Docker Environment..."

# Generate Laravel application key
echo "🔑 Generating Laravel application key..."
php artisan key:generate --show > /tmp/app_key.txt
APP_KEY=$(cat /tmp/app_key.txt)
rm /tmp/app_key.txt

# Update .env.docker with the generated key
sed -i.bak "s|APP_KEY=base64:your-app-key-here|APP_KEY=$APP_KEY|g" .env.docker
echo "✅ Application key updated in .env.docker"

# Create necessary directories
echo "📁 Creating necessary directories..."
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Set proper permissions
echo "🔒 Setting proper permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod -R 644 storage/logs/.gitignore

echo "✅ Docker setup completed!"
echo ""
echo "🐳 To start the application, run:"
echo "   docker compose up -d"
echo ""
echo "📋 To view logs:"
echo "   docker compose logs -f"
echo ""
echo "🗄️  To run migrations:"
echo "   docker compose exec app php artisan migrate"
echo ""
echo "🌐 The application will be available at: http://localhost:8000"
