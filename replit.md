# URL Shortener - PHP + PostgreSQL

## Overview
A minimal resource PHP + PostgreSQL based URL shortener with public access, user authentication via OTP email, admin panel, and 3-step external widget flow for redirect handling.

## Project Structure
```
/public          - Public pages (homepage, redirect handler)
/user            - User authentication and dashboard
/admin           - Admin panel for managing all links and users
/api             - API endpoints for widget integration
/widgets         - External widget pages (3-step flow)
db.php           - Database connection
index.php        - Main router for clean URLs
```

## Key Features
1. **Public URL Shortening** - No login required, duplicate URLs return same short code
2. **User System** - OTP email signup/login, personal dashboard with click tracking
3. **Admin Panel** - Manage all links/users, create custom short codes, search functionality
4. **3-Step Widget Flow** - External pages with timers before final redirect
5. **API Integration** - REST API for fetching destination URLs

## Database Schema
- **users**: User accounts with OTP verification and admin flags
- **links**: Short URLs with click counting, linked to users (nullable)

## Recent Changes
- Initial project setup (Oct 18, 2025)
- PostgreSQL database created with users and links tables
- All core pages built (public, user, admin, API, widgets)
- Router configured for clean short URLs

## Admin Access
To create an admin user, manually update the database:
```sql
UPDATE users SET is_admin = true WHERE email = 'your@email.com';
```

## Notes
- OTP is generated client-side via JavaScript (not stored in DB)
- Click counting is simple increment on each visit
- No detailed analytics or IP logging for minimal resource usage
- PostgreSQL used instead of MySQL (Replit native support)
