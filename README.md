# NoteBrain

NoteBrain is a Laravel-based web application for collecting, organizing, and managing articles and notes. It features a
smooth user interface built with Tailwind CSS and Vite, article summarization, user preferences, and powerful APIs for
integrations and automation.

## Features

- **Authentication & User Management**
    - Secure login and registration using Laravel Sanctum and built-in auth scaffolding
    - Edit, update, and delete your user profile

- **Article Management**
    - Create, read, update, delete, archive, and restore articles
    - Rich dashboard to manage all your articles
    - Keep articles unread, mark as read, or restore to inbox
    - Article summarization feature

- **User Preferences**
    - Customize user-specific settings

- **API Endpoints**
    - RESTful API for handling articles and user accounts
    - Includes authentication routes compatible with frontend or other services

- **Modern Frontend**
    - Built with Tailwind CSS, Alpine.js, and Vite for fast, modern performance
    - Responsive design for desktop and mobile
    - Minimal, distraction-free reading experience: on article pages, the navigation bar is hidden and the back button
      becomes a floating button

## Project Structure

- **`app/Http/Controllers/`**: Laravel controllers for API, articles, dashboard, profiles, user preferences
- **`app/Models/`**: Eloquent models, including `Article`, `User`, and user/article preference relationships
- **`resources/views/`**: Blade templates for articles, authentication, dashboard, components, and layouts
- **`routes/`**:
    - `web.php`: Web application routes (authenticated article and user flows)
    - `api.php`: REST API endpoints

## Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js with npm
- (Optional but recommended) SQLite or another supported database

### Installation

1. **Clone the Repository**

```textmate
git clone <your-repo-url>
   cd notebrain
```

2. **Install PHP Dependencies**

```textmate
composer install
```

3. **Install Node Modules**

```textmate
npm install
```

4. **Copy and Configure Environment**

```textmate
cp .env.example .env
   php artisan key:generate
```

Edit `.env` for your database and mail settings if needed.

5. **Run Migrations**

```textmate
php artisan migrate
```

6. **Build Frontend Assets**

```textmate
npm run build
```

7. **Serve the Application**

```textmate
php artisan serve
```

By default, this will launch at [http://localhost:8000](http://localhost:8000)

### Development

To automatically recompile assets during development:

```textmate
npm run dev
```

## Testing

The application uses a local SQLite database for testing. The testing database is automatically created and migrated before running tests.

To run the tests:

```textmate
composer test
```

This will:
1. Clear the configuration cache
2. Create a testing SQLite database if it doesn't exist
3. Run migrations on the testing database
4. Run the tests

You can also run tests directly with:

```textmate
phpunit
```

or

```textmate
php artisan test
```

These methods will also automatically set up the testing database, as the bootstrap file in the tests directory will create the database and run migrations before the tests.

## API Usage

- Authentication: `POST /api/login` (see routes/api.php for full endpoints)
- Protected routes require a Bearer token from Sanctum authentication

See `README-API-AUTH.md` for API-specific details.

## Customization & Contribution

- The UI is easily customizable with Tailwind CSS (`resources/css/app.css`)
- JavaScript is organized in `resources/js`
- Article processing logic (including summarization) lives in `app/Services/ArticleSummarizer.php`
- Follow standard Laravel conventions for adding new features or modifying models/controllers

Contributions and bug reports are welcome!
