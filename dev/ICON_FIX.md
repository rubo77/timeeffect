# Icon Problem Fix - Docker Development Environment

## Problem Analysis
Icons are not accessible locally via `http://localhost/icons/customer.gif`

## Docker-Compose Investigation
Checking `/var/www/timeeffect/docker/docker-compose.yml`:
```yaml
app:
  volumes:
    - /var/www/timeeffect:/var/www/html
```

**Expected behavior:** `/var/www/timeeffect` is mounted as `/var/www/html` in container
**This means:** Icons should be accessible at `http://localhost/icons/customer.gif`

## Solution: Docker-Compose Restart

### Option 1: Restart Docker Services (with sudo)
```bash
cd /var/www/timeeffect/docker
sudo docker-compose down
sudo docker-compose up -d
```

### Option 2: Check Container Mount (with sudo)
```bash
cd /var/www/timeeffect/docker
sudo docker-compose exec app ls -la /var/www/html/icons/
```

### Option 3: Rebuild Container (if needed)
```bash
cd /var/www/timeeffect/docker
sudo docker-compose down
sudo docker-compose build --no-cache
sudo docker-compose up -d
```

### Option 4: Add User to Docker Group (permanent fix)
```bash
sudo usermod -aG docker $USER
# Then logout and login again
```

## Current Status
- Icons work online: https://te.eclabs.de/icons/customer.gif ✅
- Icons work in Docker: `sudo docker-compose exec app ls -la /var/www/html/icons/` ✅
- Docker containers running on ports 8081 (HTTP) and 3307 (MySQL) to avoid conflicts
- All subnav templates modernized to remove legacy image dependencies
- Breadcrumb templates fixed and functional
- **SOLUTION SUCCESSFUL:** Docker-compose restart resolved the icon mounting issue

## Final Solution Applied
```bash
cd /var/www/timeeffect/docker
sudo docker-compose down --volumes --remove-orphans
sudo docker-compose up -d --build
```

**Result:** Icons are now properly mounted and accessible within the Docker environment.
