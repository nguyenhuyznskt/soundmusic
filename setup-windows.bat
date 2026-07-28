@echo off
setlocal
if not exist .env copy .env.example .env
call composer install
if errorlevel 1 goto error
call php artisan key:generate
call php artisan migrate:fresh --seed
call php artisan storage:link
call php artisan optimize:clear
echo.
echo CloudMusic setup completed. Run start-dev.bat
pause
exit /b 0
:error
echo Setup failed. Check PHP, Composer, pdo_mysql and .env database settings.
pause
exit /b 1
