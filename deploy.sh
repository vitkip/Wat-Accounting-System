#!/bin/bash

# ============================================================================
# ລະບົບບັນຊີວັດ - Deployment Script
# ສຳລັບການ deploy ໄປ Production Server ດ້ວຍ Git
# ============================================================================

echo "🚀 Starting deployment to production server..."

# Configuration
REMOTE_USER="your_username"
REMOTE_HOST="laotemples.com"
REMOTE_PATH="/var/www/html/all"
GIT_REPO="https://github.com/vitkip/Wat-Accounting-System.git"
BRANCH="main"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Functions
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "ℹ $1"
}

# Step 1: Local checks
print_info "Step 1: Running local checks..."

if ! git diff-index --quiet HEAD --; then
    print_warning "You have uncommitted changes!"
    read -p "Do you want to continue? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_error "Deployment cancelled."
        exit 1
    fi
fi

print_success "Local checks passed"

# Step 2: Push to Git
print_info "Step 2: Pushing to Git repository..."

git add .
read -p "Enter commit message: " commit_message
git commit -m "$commit_message" || print_warning "No changes to commit"
git push origin $BRANCH

if [ $? -eq 0 ]; then
    print_success "Pushed to Git repository"
else
    print_error "Failed to push to Git"
    exit 1
fi

# Step 3: Deploy to server
print_info "Step 3: Deploying to production server..."

ssh $REMOTE_USER@$REMOTE_HOST << 'ENDSSH'
    cd /var/www/html/all
    
    echo "📥 Pulling latest changes..."
    git pull origin main
    
    echo "🔐 Setting permissions..."
    find . -type f -exec chmod 644 {} \;
    find . -type d -exec chmod 755 {} \;
    
    echo "📁 Creating logs directory if not exists..."
    mkdir -p logs
    chmod 755 logs
    chown www-data:www-data logs
    
    echo "🔄 Clearing cache if needed..."
    # Add cache clearing commands here if you implement caching
    
    echo "✅ Deployment completed!"
ENDSSH

if [ $? -eq 0 ]; then
    print_success "Deployed to production server successfully!"
else
    print_error "Deployment failed!"
    exit 1
fi

# Step 4: Run health check
print_info "Step 4: Running health check..."

HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://$REMOTE_HOST/all/health.php)

if [ "$HTTP_CODE" -eq 200 ]; then
    print_success "Health check passed (HTTP $HTTP_CODE)"
    curl -s https://$REMOTE_HOST/all/health.php | jq '.'
else
    print_error "Health check failed (HTTP $HTTP_CODE)"
    print_warning "Please check the logs on the server"
fi

echo ""
echo "🎉 Deployment process completed!"
echo "📊 Check status at: https://$REMOTE_HOST/all/health.php"
echo "🌐 Visit site at: https://$REMOTE_HOST/all/"
