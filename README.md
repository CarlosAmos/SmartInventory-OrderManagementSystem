# SmartInventory-OrderManagementSystem

# Install dependencies
composer install
npm install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Create SQLite database
touch database/database.sqlite

# Run migrations and seeders
php artisan migrate --seed

# Start the application
php artisan serve
npm run dev