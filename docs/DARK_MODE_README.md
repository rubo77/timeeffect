# Dark Mode Implementation for TimeEffect

## Overview

This implementation adds comprehensive dark mode support to the TimeEffect application with the following features:

- **System-aware theming**: Automatically detects user's system preference
- **Manual theme selection**: Users can force light or dark mode
- **Persistent preferences**: Theme choices are saved per user in the database
- **Real-time switching**: Instant theme changes without page reload
- **Comprehensive styling**: All UI elements support both light and dark themes

## Features Implemented

### 1. Theme Options
- **System Default**: Follows the user's operating system theme preference
- **Force Light Mode**: Always uses light theme regardless of system setting
- **Force Dark Mode**: Always uses dark theme regardless of system setting

### 2. User Interface Components
- ✅ **Navigation tabs**: Modern styling with proper contrast in both modes
- ✅ **Form elements**: Inputs, textareas, selects with dark theme support
- ✅ **Tables**: Data tables with alternating row colors and proper contrast
- ✅ **Buttons**: All button types with appropriate hover states
- ✅ **Cards**: Content cards with gradients and shadows
- ✅ **Alerts**: Status messages with theme-appropriate colors
- ✅ **Links**: Navigation and text links with proper contrast

### 3. Theme Switching Methods
- **Quick toggle button**: Moon/sun icon in top navigation for instant switching
- **Settings page**: Full user preferences page with theme selection
- **Dropdown selector**: Direct theme selection in demo/settings

## Files Modified/Created

### New Files
- `js/theme.js` - Theme management JavaScript class
- `user/settings.php` - User settings page with theme preferences
- `user/save-theme.php` - AJAX endpoint for theme preference saving
- `templates/user/settings.ihtml` - User settings page template
- `sql/add_theme_support.sql` - Database migration for theme preferences
- `dark-mode-demo.html` - Demonstration page (can be removed)

### Modified Files
- `css/modern.css` - Enhanced with comprehensive dark mode CSS
- `templates/*.ihtml` - All main templates updated with theme JavaScript
- `templates/shared/topnav.ihtml` - Added theme toggle button and settings link

## Database Changes

Run the following SQL migration to add theme support:

```sql
-- Add theme_preference column to auth table
ALTER TABLE `auth` ADD COLUMN `theme_preference` ENUM('light', 'dark', 'system') NOT NULL DEFAULT 'system' AFTER `facsimile`;

-- Add index for better performance
ALTER TABLE `auth` ADD INDEX `theme_preference` (`theme_preference`);
```

## Usage Instructions

### For Users
1. **Quick Toggle**: Click the moon/sun icon (🌙/☀️) in the top navigation to instantly switch between light and dark modes
2. **Settings Page**: Click the settings icon (⚙️) in the top navigation to access full user preferences
3. **Theme Selection**: Choose from:
   - System Default (follows your device's theme)
   - Force Light Mode (always light)
   - Force Dark Mode (always dark)

### For Developers
1. **Theme Detection**: The JavaScript automatically detects and applies the user's preferred theme
2. **CSS Variables**: All theming uses CSS custom properties for easy maintenance
3. **Server Integration**: Theme preferences are saved via AJAX and persist across sessions

## Technical Implementation

### CSS Architecture
The dark mode implementation uses CSS custom properties (variables) with data attributes:

```css
/* Light mode (default) */
:root {
    --primary-color: #6366f1;
    --background-color: #f1f5f9;
    --text-primary: #334155;
}

/* Dark mode */
[data-theme="dark"] {
    --primary-color: #818cf8;
    --background-color: #0f172a;
    --text-primary: #f1f5f9;
}

/* System preference dark mode */
@media (prefers-color-scheme: dark) {
    :root:not([data-theme="light"]) {
        /* Dark mode variables */
    }
}
```

### JavaScript Theme Manager
The `ThemeManager` class handles:
- Theme detection and application
- User preference persistence
- UI state synchronization
- Server communication

### PHP Integration
- User theme preferences stored in `auth.theme_preference` column
- Settings page for user preference management
- AJAX endpoint for real-time theme saving

## Browser Support

- ✅ Modern browsers with CSS custom properties support
- ✅ `prefers-color-scheme` media query support
- ✅ JavaScript ES6+ features
- ✅ Mobile and desktop responsive design

## Testing

Use the included `dark-mode-demo.html` file to test all theme functionality:

1. Start a local server: `python3 -m http.server 8080`
2. Open `http://localhost:8080/dark-mode-demo.html`
3. Test theme switching with the toggle button and dropdown
4. Verify all UI components display correctly in both themes

## Future Enhancements

- High contrast mode support
- Additional theme variants (e.g., blue, green themes)
- Theme scheduling (automatic switching based on time)
- Accessibility improvements for low vision users

## Troubleshooting

### Theme Not Persisting
- Ensure database migration has been run
- Check that user is logged in when changing themes
- Verify AJAX endpoint `/user/save-theme.php` is accessible

### Styling Issues
- Clear browser cache after CSS updates
- Check browser console for JavaScript errors
- Verify CSS custom properties are supported

### Performance
- Theme switching should be instant
- No page reloads required for theme changes
- Minimal JavaScript overhead with efficient CSS