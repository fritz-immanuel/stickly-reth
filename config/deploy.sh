#!/bin/bash

# Exit on any error
set -e

# Define variables
REPO_DIR="/home13/procuret/public_html"  # Update to the actual repo path
BRANCH="dev"

echo "Starting deployment..."

# Go to the repo directory
cd "$REPO_DIR"

# Reset any local changes (optional but safe for automated deployment)
git reset --hard

# Pull the latest from dev branch
git fetch origin $BRANCH
git checkout $BRANCH
git pull origin $BRANCH

echo "Deployment complete!"
