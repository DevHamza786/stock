# Laravel 10 Admin Authentication System

This project includes a secure admin-only authentication system built with Laravel 10 and Laravel Breeze.

## Features

- **Admin-Only Access**: Single admin user account with secure login
- **User Login**: Secure login with email and password
- **Password Reset**: Forgot password functionality with email verification
- **Profile Management**: Admin can update profile information and change passwords
- **Session Management**: Remember me functionality and secure logout
- **Modern UI**: Beautiful, responsive design built with Tailwind CSS
- **Registration Disabled**: Only admin user exists, no public registration

## Installation

1. **Clone the repository** (if not already done):
   ```bash
   git clone <repository-url>
   cd Azeem
   ```

2. **Install dependencies**:
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**:
   ```bash
   php artisan migrate
   ```

5. **Build assets**:
   ```bash
   npm run build
   ```

6. **Start the development server**:
   ```bash
   php artisan serve
   ```

## Usage

### Accessing the Application

- **Home Page**: `http://localhost:8000` (redirects to login)
- **Login**: `http://localhost:8000/login`
- **Dashboard**: `http://localhost:8000/dashboard` (requires authentication)

### Admin Credentials

- **Email**: `admin@admin.com`
- **Password**: `admin123`

### Authentication Routes

- `GET /` - Home page (redirects to login)
- `GET /login` - Show login form
- `POST /login` - Process login
- `POST /logout` - Logout user
- `GET /dashboard` - Protected admin dashboard (requires auth)
- `GET /profile` - Admin profile management

### Password Reset

- `GET /forgot-password` - Show forgot password form
- `POST /forgot-password` - Send password reset email
- `GET /reset-password/{token}` - Show reset password form
- `POST /reset-password` - Process password reset

## Project Structure

### Controllers
- `app/Http/Controllers/Auth/` - Authentication controllers
  - `AuthenticatedSessionController.php` - Login/logout
  - `RegisteredUserController.php` - User registration
  - `PasswordResetLinkController.php` - Password reset requests
  - `NewPasswordController.php` - Password reset processing
  - `EmailVerificationPromptController.php` - Email verification
  - `ProfileController.php` - User profile management

### Views
- `resources/views/auth/` - Authentication views
  - `login.blade.php` - Login form
  - `register.blade.php` - Registration form
  - `forgot-password.blade.php` - Password reset request
  - `reset-password.blade.php` - Password reset form
  - `verify-email.blade.php` - Email verification
- `resources/views/dashboard.blade.php` - Protected dashboard
- `resources/views/profile/` - Profile management views

### Models
- `app/Models/User.php` - User model with authentication features

### Middleware
- `auth` - Requires authentication
- `guest` - Requires guest (not authenticated)
- `verified` - Requires verified email

## Security Features

- Password hashing using Laravel's built-in hashing
- CSRF protection on all forms
- Rate limiting on authentication routes
- Secure session management
- Password confirmation for sensitive actions
- Email verification (optional)

## Customization

### Adding Custom Fields

To add custom fields to user registration:

1. **Update the migration**:
   ```bash
   php artisan make:migration add_custom_fields_to_users_table
   ```

2. **Update the User model**:
   ```php
   protected $fillable = [
       'name',
       'email', 
       'password',
       'your_custom_field',
   ];
   ```

3. **Update the registration form** in `resources/views/auth/register.blade.php`

4. **Update the RegisteredUserController** to handle the new field

### Styling

The project uses Tailwind CSS. To customize the appearance:

1. Modify the Blade components in `resources/views/components/`
2. Update Tailwind classes in the view files
3. Run `npm run build` to compile changes

## Testing

Run the test suite:
```bash
php artisan test
```

## Production Deployment

1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false` in `.env`
3. Configure your production database
4. Run `php artisan config:cache`
5. Run `php artisan route:cache`
6. Run `php artisan view:cache`
7. Build production assets: `npm run build`

## Support

For Laravel documentation, visit: https://laravel.com/docs
For Laravel Breeze documentation, visit: https://laravel.com/docs/starter-kits#laravel-breeze
