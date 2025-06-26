@echo off
echo Starting Laravel Queue Worker for Parking System...
echo Press Ctrl+C to stop
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
pause