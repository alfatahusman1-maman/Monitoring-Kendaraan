@echo off
echo ========================================
echo   Auto Push to GitHub - Monitoring Kendaraan
echo ========================================

:: Ambil pesan commit dari input user atau gunakan default
set /p commit_msg="Masukkan pesan commit (kosongkan untuk 'Update sistem'): "
if "%commit_msg%"=="" set commit_msg=Update sistem

echo.
echo [+] Menambahkan perubahan...
git add .

echo [+] Melakukan commit: "%commit_msg%"
git commit -m "%commit_msg%"

echo [+] Push ke GitHub...
git push origin main

echo.
echo ========================================
echo   Selesai! Perubahan telah ter-push.
echo ========================================
pause
