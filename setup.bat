@echo off
echo ========================================
echo  MiniApp API - Setup Script
echo ========================================

echo.
echo [1/6] Copying environment file...
if not exist .env (
    copy .env.example .env
    echo .env file created successfully
) else (
    echo .env file already exists
)

echo.
echo [2/6] Installing Composer dependencies...
composer install --no-dev --optimize-autoloader

echo.
echo [3/6] Generating application key...
php artisan key:generate

echo.
echo [4/6] Publishing Sanctum configuration...
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

echo.
echo [5/6] Publishing Spatie Permission configuration...
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

echo.
echo [6/6] Running migrations and seeders...
php artisan migrate --seed

echo.
echo Creating storage link...
php artisan storage:link

echo.
echo ========================================
echo  Setup completed successfully!
echo ========================================
echo.
echo Demo accounts:
echo - Admin: admin@miniapp.com / admin123
echo - Coffee Owner: coffee@owner.com / coffee123  
echo - Beauty Owner: beauty@owner.com / beauty123
echo - End Users: user1@example.com to user20@example.com / user123
echo.
echo To start the server: php artisan serve
echo API Base URL: http://localhost:8000/api/v1
echo.
pause
