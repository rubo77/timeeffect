# Unified Header System for TimeEffect Templates

## Overview

The unified header system centralizes all HTML head elements, CSS includes, and favicon management into a single reusable component: `templates/shared/header.ihtml.php`.

## Features

### Dynamic Favicon Management
The header automatically selects the appropriate favicon based on page context:

- **start.png** - For new effort pages (action=new, action=add, or efforts.php without eid)
- **stop.png** - For stop all pages (stop_all=1 or action=stop_all)
- **favicon.png** - Default for all other pages

### Automatic Theme Integration
- Detects user theme preference from authentication system
- Sets `data-theme` attribute on HTML element
- Supports system, light, and dark themes

### Responsive Design
- Mobile viewport meta tags
- PWA-ready meta tags
- Responsive CSS includes

### Consistent CSS Loading
- project.css (core styles)
- responsive.css (if available)
- modern.css (modern UI framework)
- layout.css (layout system)

## Usage

### Basic Usage
Replace existing header sections in templates with:

```php
<?php
// Include unified header
include_once(__DIR__ . '/shared/header.ihtml.php');
?>
```

### Custom Favicon
Set a custom favicon before including the header:

```php
<?php
$favicon = $GLOBALS['_PJ_image_path'] . '/custom-icon.png';
include_once(__DIR__ . '/shared/header.ihtml.php');
?>
```

### Custom Page Title
Set the page title before including the header:

```php
<?php
$center_title = 'Custom Page Title';
include_once(__DIR__ . '/shared/header.ihtml.php');
?>
```

### Additional Head Content
Add custom head content:

```php
<?php
$additional_head_content = '<meta name="custom" content="value">';
include_once(__DIR__ . '/shared/header.ihtml.php');
?>
```

### Custom Body Attributes
Set custom body attributes:

```php
<?php
$body_attributes = 'class="custom-page" onload="initPage()"';
include_once(__DIR__ . '/shared/header.ihtml.php');
?>
```

### Additional Body Scripts
Add scripts after body opening:

```php
<?php
$additional_body_scripts = '<script>console.log("Page loaded");</script>';
include_once(__DIR__ . '/shared/header.ihtml.php');
?>
```

## Migration Guide

### Before (Old Pattern)
```php
<HTML>
<HEAD>
<TITLE>TIMEEFFECT - <?= $center_title; ?></TITLE>
<LINK REL="stylesheet" HREF="<?php print $_PJ_css_path; ?>/project.css" TYPE="text/css">
<LINK REL="stylesheet" HREF="<?php print $_PJ_css_path; ?>/modern.css" TYPE="text/css">
<LINK REL="stylesheet" HREF="<?php print $_PJ_css_path; ?>/layout.css" TYPE="text/css">
</HEAD>
<SCRIPT SRC="<?php print $_PJ_http_root; ?>/include/functions.js"></SCRIPT>
<BODY>
```

### After (New Pattern)
```php
<?php
// Include unified header
include_once(__DIR__ . '/shared/header.ihtml.php');
?>
```

## Benefits

1. **DRY Principle** - Single source of truth for header elements
2. **Consistent Styling** - All pages use the same CSS loading order
3. **Dynamic Favicons** - Context-aware icon selection
4. **Theme Integration** - Automatic theme preference detection
5. **Maintainability** - Changes to header affect all pages
6. **Mobile Ready** - Responsive meta tags included by default

## Files Updated

The following templates have been updated to use the unified header:

- `templates/note.ihtml.php`
- `templates/list.ihtml.php`
- `templates/error.ihtml.php`
- `templates/add.ihtml.php`
- `templates/delete.ihtml.php`
- `templates/shared/login.ihtml.php`

## Remaining Files

Many template files still need manual migration due to varying header patterns. Use the provided update script as a starting point:

```bash
php scripts/update_headers.php
```

## Testing

After migration, verify:

1. Pages load correctly
2. CSS styles are applied
3. Favicons appear correctly based on context
4. Theme switching works
5. Mobile responsiveness is maintained

## Troubleshooting

### Common Issues

1. **Incorrect include path** - Ensure the relative path to `shared/header.ihtml.php` is correct
2. **Missing variables** - Set `$center_title` before including header
3. **CSS not loading** - Check that `$_PJ_css_path` is properly set
4. **Favicon not showing** - Verify image files exist in `$_PJ_image_path`

### Debug Tips

- Check browser developer tools for 404 errors on CSS/JS files
- Verify HTML structure is valid after header inclusion
- Test favicon logic by checking different page contexts
