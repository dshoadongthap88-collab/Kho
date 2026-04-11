@echo off
:: Di chuyen den thu muc hien tai
cd /d "%~dp0"

echo [1/3] Dang chay: npm run build...
call npm run build

echo.
echo [2/3] Dang khoi dong Vite Dev Server (Cua so moi)...
start "Vite Dev Server" cmd /c "npm run dev"

echo.
echo [3/3] Dang khoi dong Laravel Artisan Serve...
echo Nhan Ctrl+C de dung server
php artisan serve  --port=8000
