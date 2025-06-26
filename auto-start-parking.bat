@echo off
cd /d "C:\Users\axiom\Desktop\park-mmu"
timeout /t 30 /nobreak
php artisan queue:work --sleep=3 --tries=3 --max-time=3600 --quiet