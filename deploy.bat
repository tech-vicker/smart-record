@echo off
echo SmartFarm Deployment Preparation Tool
echo ================================
echo.

REM Create deployment directory structure
if not exist "smartfarm\deployment" mkdir "smartfarm\deployment"
if not exist "smartfarm\deployment\db" mkdir "smartfarm\deployment\db"
if not exist "smartfarm\deployment\css" mkdir "smartfarm\deployment\css"
if not exist "smartfarm\deployment\js" mkdir "smartfarm\deployment\js"
if not exist "smartfarm\deployment\includes" mkdir "smartfarm\deployment\includes"

REM Copy all essential files
copy "smartfarm\*.php" "smartfarm\deployment\" /Y
copy "smartfarm\css\*" "smartfarm\deployment\css\" /Y
copy "smartfarm\js\*" "smartfarm\deployment\js\" /Y
copy "smartfarm\includes\*" "smartfarm\deployment\includes\" /Y
copy "smartfarm\.htaccess" "smartfarm\deployment\" /Y

REM Create empty database
if not exist "smartfarm\deployment\db\database.sqlite" (
    echo Creating empty database...
    type nul > "smartfarm\deployment\db\database.sqlite"
)

REM Copy deployment files
copy "smartfarm\deployment\*" "smartfarm\deployment\" /Y

echo.
echo ✓ Deployment folder prepared at smartfarm/deployment/
echo ✓ Empty database created: deployment/db/database.sqlite
echo.
echo Next steps:
echo 1. ZIP the CONTENTS of deployment/ folder (not the folder itself)
echo 2. Upload ZIP contents to hosting public_html
echo 3. Set permissions: folders 755, files 644, db/ 755, database.sqlite 644
echo 4. Test at your domain
echo.
pause
