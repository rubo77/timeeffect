# TimeEffect Strict Mode Compatibility

This document describes the changes made to prepare TimeEffect for strict PHP error reporting mode.

## Problem

The TimeEffect application was written for older PHP versions that used `register_globals`, which automatically made HTTP request parameters available as global variables. In modern PHP with strict error reporting (`E_ALL | E_STRICT`), these variables are undefined and cause warnings/errors.

## Solution

All variables that come from HTTP requests have been properly initialized from the `$_REQUEST`, `$_GET`, or `$_POST` superglobals with appropriate default values.

## Changes Made

### 1. Core Configuration Files

**bootstrap.php**
- Updated error reporting from `E_ALL` to `E_ALL | E_STRICT` for development mode

**include/aperetiv.inc.php**
- Updated error reporting from `E_ALL` to `E_ALL | E_STRICT`
- Added initialization for all request variables:
  - `$lang`, `$last` (core variables)
  - Expansion variables: `$exc`, `$coc`, `$exp`, `$cop`, `$exca`, `$coca`, `$expa`, `$copa`
  - Show variables: `$sic`, `$scp`, `$sbe`
- Initialized session arrays: `$expanded` and `$shown` from `$_SESSION`

### 2. Main Application Files

**inventory/customer.php**
- Added initialization for: `$new`, `$edit`, `$rates`, `$altered`, `$delete`, `$cancel`, `$confirm`, `$name`, `$customer_logo`

**statistic/customer.php**
- Added initialization for: `$cid`, `$expand`, `$pid`

**statistic/projects.php**  
- Added initialization for: `$cid`, `$pid`, `$eid`, `$expand`, `$pdf`, `$shown`
- Fixed typo: `$cutomer` → `$customer`

**user/index.php**
- Added initialization for: `$uid`, `$new`, `$edit`, `$altered`, `$delete`, `$cancel`, `$confirm`, `$id`

**user/own.php**
- Added initialization for: `$altered`, `$id`, `$telephone`, `$facsimile`, `$email`, `$password`, `$password_retype`

**admin/pdflayout.php**
- Added initialization for: `$altered`, `$pdflayout`

**groups/index.php**
- Added initialization for: `$gid`, `$new`, `$edit`, `$altered`, `$delete`, `$cancel`, `$confirm`, `$id`

**report/index.php**
- Added initialization for: `$pdf`, `$smonth`, `$sday`, `$emonth`, `$eday`

### 3. Variable Initialization Pattern

All variables are initialized using the null coalescing operator (`??`) with appropriate defaults:

```php
// For boolean flags (form actions)
$new = $_REQUEST['new'] ?? null;
$edit = $_REQUEST['edit'] ?? null;
$altered = $_REQUEST['altered'] ?? null;

// For string data
$cid = $_REQUEST['cid'] ?? '';
$customer_name = $_REQUEST['customer_name'] ?? '';

// For arrays
$name = $_REQUEST['name'] ?? [];
$shown = $_REQUEST['shown'] ?? [];
```

## Files Not Modified

The following categories of files were not modified as they don't have real issues:

1. **Function parameter defaults**: Files like `include/functions.inc.php` and `include/data.inc.php` where variables are function parameters with default values
2. **Third-party libraries**: PEAR library files in `include/pear/` are external dependencies  
3. **Generated/compiled libraries**: Files like `include/fpdf.inc.php`, `include/cpdf.inc.php`, `include/cezpdf.inc.php`

## Testing

The application has been tested to ensure:
- ✅ All syntax checks pass with strict error reporting
- ✅ Variable initialization code is properly in place
- ✅ Error reporting is set to `E_ALL | E_STRICT` in development mode
- ✅ No uninitialized variable warnings in main application flow

## Usage

To enable strict mode:

1. **Development mode**: Set `APP_ENV=development` in your `.env` file
2. **Manual override**: The strict mode is now enabled by default in `include/aperetiv.inc.php`

The application will now display all errors, warnings, and notices when `error_reporting` is set to strict mode.