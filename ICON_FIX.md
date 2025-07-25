# Icon Problem Fix - Local Development

## Problem
Icons are not accessible locally via `http://localhost/icons/customer.gif` because:
- Nginx DocumentRoot is `/var/www/html`
- TimeEffect is located in `/var/www/timeeffect`
- Icons exist in `/var/www/timeeffect/icons/` but are not web-accessible

## Solution Options

### Option 1: Create Symlink (requires sudo)
```bash
sudo ln -sf /var/www/timeeffect/icons /var/www/html/icons
sudo ln -sf /var/www/timeeffect/images /var/www/html/images
sudo ln -sf /var/www/timeeffect/css /var/www/html/css
```

### Option 2: Nginx Configuration
Add to nginx virtual host config:
```nginx
location /icons/ {
    alias /var/www/timeeffect/icons/;
}
location /images/ {
    alias /var/www/timeeffect/images/;
}
location /css/ {
    alias /var/www/timeeffect/css/;
}
```

### Option 3: Copy Files (temporary)
```bash
cp -r /var/www/timeeffect/icons /var/www/html/
cp -r /var/www/timeeffect/images /var/www/html/
cp -r /var/www/timeeffect/css /var/www/html/
```

## Current Status
- Icons work online: https://te.eclabs.de/icons/customer.gif ✅
- Icons fail locally: http://localhost/icons/customer.gif ❌
- All subnav templates modernized to remove legacy image dependencies
- Breadcrumb templates fixed and functional
