# Railway Deployment Guide for OES

## Prerequisites
1. Railway account (sign up at https://railway.app)
2. GitHub account connected to Railway
3. OES_Deploy repository ready

## Deployment Steps

### 1. Create New Project on Railway
1. Go to https://railway.app/dashboard
2. Click "New Project"
3. Select "Deploy from GitHub repo"
4. Choose `abre0101/OES_Deploy` repository
5. Click "Deploy Now"

### 2. Add MySQL Database
1. In your Railway project, click "New"
2. Select "Database" → "Add MySQL"
3. Railway will automatically create a MySQL database
4. Note the connection details from the "Variables" tab

### 3. Configure Environment Variables
In Railway project settings, add these variables:

```
MYSQL_HOST=<from Railway MySQL service>
MYSQL_PORT=<from Railway MySQL service>
MYSQL_DATABASE=<from Railway MySQL service>
MYSQL_USER=<from Railway MySQL service>
MYSQL_PASSWORD=<from Railway MySQL service>
MYSQL_URL=<from Railway MySQL service>

# Application Settings
APP_ENV=production
PHP_VERSION=8.2
```

### 4. Update Database Connection File
The `Connections/OES.php` file should use Railway environment variables:

```php
<?php
// Railway MySQL Connection
$host = getenv('MYSQL_HOST') ?: 'localhost';
$port = getenv('MYSQL_PORT') ?: '3306';
$database = getenv('MYSQL_DATABASE') ?: 'railway';
$username = getenv('MYSQL_USER') ?: 'root';
$password = getenv('MYSQL_PASSWORD') ?: '';

$con = new mysqli($host, $username, $password, $database, $port);

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

$con->set_charset("utf8mb4");
return $con;
?>
```

### 5. Import Database Schema
1. Connect to Railway MySQL using a MySQL client (e.g., MySQL Workbench, phpMyAdmin)
2. Import your database schema from `database/` folder
3. Or use Railway's MySQL console to run SQL scripts

### 6. Deploy
1. Push changes to `OES_Deploy` repository:
   ```bash
   git add -A
   git commit -m "Configure for Railway deployment"
   git push deploy main
   ```
2. Railway will automatically detect changes and redeploy
3. Wait for deployment to complete (check logs)

### 7. Access Your Application
1. Go to Railway project settings
2. Click "Generate Domain" to get a public URL
3. Your app will be available at: `https://your-app.up.railway.app`

## Important Files for Railway

- `nixpacks.toml` - Defines PHP version and extensions
- `railway.json` - Railway-specific configuration
- `.htaccess` - Apache configuration (if needed)
- `Connections/OES.php` - Database connection with environment variables

## Troubleshooting

### Database Connection Issues
- Check environment variables are set correctly
- Verify MySQL service is running
- Check connection string format

### Deployment Fails
- Check Railway logs for errors
- Verify `nixpacks.toml` syntax
- Ensure all required PHP extensions are listed

### Application Errors
- Enable error reporting temporarily
- Check Railway logs
- Verify file permissions

## Monitoring
- Railway provides built-in logs
- Monitor resource usage in Railway dashboard
- Set up alerts for downtime

## Automatic Deployments
Railway automatically deploys when you push to the connected GitHub repository:
```bash
git push deploy main
```

## Cost
- Railway offers a free tier with limitations
- Monitor usage to avoid unexpected charges
- Upgrade to paid plan if needed

## Support
- Railway Docs: https://docs.railway.app
- Railway Discord: https://discord.gg/railway
- GitHub Issues: https://github.com/abre0101/OES_Deploy/issues
