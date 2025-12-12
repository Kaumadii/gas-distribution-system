# Gas Distribution Management System

A comprehensive Laravel-based web application for managing gas distribution operations including suppliers, purchase orders, inventory, customer orders, and delivery routes.

## Features

- **Supplier Management**: Manage suppliers, rates, and payments
- **Purchase Orders**: Create and track purchase orders from suppliers
- **Goods Received Notes (GRN)**: Record and approve received goods
- **Inventory Management**: Track gas cylinder stock levels
- **Customer Management**: Manage customers and their pricing
- **Order Processing**: Create and manage customer orders
- **Delivery Routes**: Plan and track delivery routes
- **Supplier Tracking**: Monitor supplier performance and history
- **Payment Management**: Track supplier payments and ledger

## Requirements

- **PHP**: 8.2 or higher
- **Composer**: Latest version
- **Node.js**: 18.x or higher
- **npm**: 9.x or higher
- **SQLite**: (Default) or MySQL/PostgreSQL

## Installation Steps

### 1. Clone the Repository

```bash
git clone <repository-url>
cd gas-distribution
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node.js Dependencies

```bash
npm install
```

### 4. Environment Configuration

Create a `.env` file from the example (if it doesn't exist):

```bash
cp .env.example .env
```

Or manually create a `.env` file with the following minimum configuration:

```env
APP_NAME="Gas Distribution"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=sqlite
# DB_DATABASE=database/database.sqlite (default for SQLite)

SESSION_DRIVER=database
SESSION_LIFETIME=120
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Database Setup

The application uses SQLite by default. Ensure the database file exists:

```bash
touch database/database.sqlite
```

Or if using MySQL/PostgreSQL, update your `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gas_distribution
DB_USERNAME=root
DB_PASSWORD=
```

### 7. Run Database Migrations

```bash
php artisan migrate
```

### 8. Seed the Database (Optional)

Seed the database with initial data including default admin user:

```bash
php artisan db:seed
```

This will create:
- Admin user: `admin@example.com` / `password`
- Default gas types (2.8kg, 5kg, 12.5kg)
- Sample supplier and customer data
- Initial stock levels

### 9. Build Frontend Assets

For production:

```bash
npm run build
```

For development (with hot reload):

```bash
npm run dev
```

### 10. Start the Development Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## Quick Setup (All-in-One)

You can also use the composer setup script to automate most steps:

```bash
composer run setup
```

This will:
- Install Composer dependencies
- Copy `.env.example` to `.env` (if it doesn't exist)
- Generate application key
- Run migrations
- Install npm dependencies
- Build frontend assets

## Development

### Running in Development Mode

For a complete development environment with hot reload, queue worker, and logs:

```bash
composer run dev
```

This starts:
- Laravel development server
- Queue worker
- Log viewer (Pail)
- Vite dev server with hot reload

### Running Tests

```bash
composer run test
```

Or directly:

```bash
php artisan test
```

## Default Login Credentials

After seeding the database:

- **Email**: `admin@example.com`
- **Password**: `password`

**⚠️ Important**: Change the default password after first login in production environments.

## Project Structure

```
gas-distribution/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # Application controllers
│   │   └── Middleware/      # Custom middleware
│   └── Models/              # Eloquent models
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/             # Database seeders
├── resources/
│   ├── views/               # Blade templates
│   ├── css/                 # Stylesheets
│   └── js/                  # JavaScript files
├── routes/
│   └── web.php              # Web routes
├── public/                  # Public assets
└── config/                  # Configuration files
```

## Key Routes

- `/login` - Login page
- `/dashboard` - Main dashboard (requires authentication)
- `/suppliers` - Supplier management
- `/purchase-orders` - Purchase order management
- `/grn` - Goods Received Notes
- `/customers` - Customer management
- `/orders` - Order management
- `/routes` - Delivery route management
- `/supplier-payments` - Payment management
- `/supplier-tracking` - Supplier tracking

## Technologies Used

- **Backend**: Laravel 12
- **Frontend**: Bootstrap 5, Tailwind CSS 4
- **Build Tool**: Vite
- **Database**: SQLite (default), MySQL/PostgreSQL supported
- **PHP**: 8.2+

## Troubleshooting

### Permission Issues

If you encounter permission issues with storage or cache:

```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

### Database Connection Issues

- Ensure SQLite file exists: `touch database/database.sqlite`
- Check file permissions on `database/database.sqlite`
- Verify `.env` configuration matches your database setup

### Frontend Assets Not Loading

- Run `npm run build` to compile assets
- Or use `npm run dev` for development with hot reload
- Clear cache: `php artisan cache:clear && php artisan config:clear`

### Application Key Error

If you see "No application encryption key" error:

```bash
php artisan key:generate
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For issues, questions, or contributions, please open an issue in the repository.
