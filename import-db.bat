@echo off
echo Importing database to Railway MySQL...
"C:\Program Files\MySQL\MySQL Server 8.4\bin\mysql.exe" -h yamanote.proxy.rlwy.net -P 25317 -u root -pWVfbKCqYyoVFszxfuaEgmGkTdSkxaLWk railway < database\oes_professional.sql
echo.
echo Import completed!
pause
