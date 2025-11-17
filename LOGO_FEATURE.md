# Customer Logo Upload Feature

## Overview

This feature allows logged-in customers to upload their own company logo through the settings page, which will then be displayed in the sidebar instead of the default logo.

## Implementation Details

### Database Changes

-   Added `logo_path` column to the `users` table (migration: `2025_11_14_123600_add_logo_to_users_table.php`)
-   Added `logo_path` column to the `customers` table (migration: `2025_11_14_123500_add_logo_to_customers_table.php`)

### New Components

1. **Logo Settings Component** (`app/Livewire/Settings/Logo.php`)

    - Handles logo upload, validation, and removal
    - Uses Livewire's file upload functionality
    - Stores logos in the public disk under `logos/` directory
    - Validates file type (images only) and size (max 2MB)

2. **Logo Settings View** (`resources/views/livewire/settings/logo.blade.php`)
    - Displays current logo with remove button
    - File upload interface with drag-and-drop support
    - Success/error messaging

### Modified Components

1. **App Logo Component** (`resources/views/components/app-logo.blade.php`)

    - Now checks for user logo first, falls back to default logo
    - Displays user's custom logo if available

2. **Settings Layout** (`resources/views/components/settings/layout.blade.php`)

    - Added "Logo" menu item in settings navigation

3. **Routes** (`routes/web.php`)

    - Added route for logo settings page: `/settings/logo`

4. **User Model** (`app/Models/User.php`)
    - Added `logo_path` to fillable attributes

## Usage

### For Customers

1. Log in to your account
2. Navigate to Settings → Logo
3. Click "Choose File" to select your logo image
4. Click "Upload Logo" to save changes
5. Your logo will immediately appear in the sidebar

### File Requirements

-   Format: PNG, JPG, JPEG, GIF
-   Maximum size: 2MB
-   Recommended dimensions: Square or rectangular logos work best

### Features

-   **Upload**: Customers can upload their company logo
-   **Preview**: See current logo before uploading new one
-   **Remove**: Delete current logo and revert to default
-   **Auto-refresh**: Page refreshes automatically after logo changes
-   **Validation**: File type and size validation with user-friendly error messages

## Technical Notes

-   Logos are stored in `storage/app/public/logos/` directory
-   Public storage link must be created (`php artisan storage:link`)
-   Uses Laravel's built-in file storage system
-   Implements proper file cleanup when logos are removed or replaced

## Security

-   Only authenticated users can upload logos
-   File validation prevents malicious file uploads
-   Proper file permissions on storage directory
-   Automatic cleanup of old logos when replaced
