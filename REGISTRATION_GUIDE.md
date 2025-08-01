# Email-Only Registration - Usage Guide

## Overview
This implementation adds email-only registration to TimeEffect, using the same token system as password recovery.

## How it works

### Step 1: Email-Only Registration
1. User visits `/register.php` 
2. Sees simplified form with only email field
3. Enters email address and clicks "Register"
4. System creates minimal user record with `confirmation_token`
5. Email sent with registration completion link

### Step 2: Registration Completion  
1. User clicks link in email: `/register.php?complete=1&token=<token>`
2. System validates token and shows completion form
3. User fills in username, password, name, and other details
4. System updates user record and marks as confirmed
5. Registration complete - user can now login

## Key Features

- **Security**: Uses same secure token system as password recovery
- **Minimal Data**: Only email required initially
- **Token Expiration**: Tokens are single-use and validated
- **Duplicate Prevention**: Checks for existing email addresses
- **Group Security**: Validates group memberships against database
- **Backward Compatible**: Legacy full-form registration still works

## Database Changes
Uses existing columns from migration:
- `confirmation_token` - stores registration token
- `confirmed` - marks completed registrations
- No new database changes required

## Configuration
Controlled by existing config variables:
- `$_PJ_allow_registration` - enables/disables registration
- `$_PJ_registration_email_confirm` - enables token-based flow

## Email Template
Registration completion email includes:
- Clear instructions
- Direct link to completion form
- Security notice about ignoring unwanted emails