# Railway Deployment Guide for OES

## Prerequisites
1. Railway account (sign up at https://railway.app)
2. GitHub repository: https://github.com/abre0101/OES_Deploy.git
3. MySQL database (Railway provides this)

## Deployment Steps

### Step 1: Create New Project on Railway
1. Go to https://railway.app/new
2. Click "Deploy from GitHub repo"
3. Select repository: `abre0101/OES_Deploy`
4. Railway will automatically detect the configuration

### Step 2: Add MySQL Database
1. In your Railway project, click "+ New"
2. Select "Database" → "Add MySQL"
3. Railway will provision a MySQL database
4. Note the connection details from the "Variables" tab

### Step 3: Configure Environment Variables
Add these variables in Railway project settings:

```
DB_HOST=<your-railway-mysql-host>
DB_PORT=<your-railway-mysql-port>
DB_NAME=<your-database-name>
DB_USER=<your-database-user>
DB_PASSWORD=<your-database-password>
PORT=8080
```

### Step 4: Update Database Connection
The `Connections/OES.php` file should read from environment variables:

```php
<?php
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_NAME') ?: 'oes_db';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

$con = new mysqli($host, $username, $password, $dbname, $port);

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

return $con;
?>
```

### Step 5: Import Database Schema
1. Connect to Railway MySQL using a client (MySQL Workbench, phpMyAdmin, etc.)
2. Import your database schema from `database/` folder
3. Ensure all tables are created properly

### Step 6: Deploy
1. Railway will automatically deploy when you push to GitHub
2. Or click "Deploy" in Railway dashboard
3. Wait for build to complete
4. Access your app at the provided Railway URL

## Configuration Files

### railway.json
Configures Railway deployment settings:
- Build: Uses Nixpacks builder
- Start command: PHP built-in server
- Restart policy: On failure with max 10 retries

### nixpacks.toml
Specifies PHP version and extensions:
- PHP 8.2
- mysqli, pdo, pdo_mysql
- mbstring, zip, gd

## Post-Deployment

### 1. Test the Application
- Visit the Railway URL
- Test login functionality
- Verify database connections
- Check all features

### 2. Set Up Custom Domain (Optional)
1. Go to Railway project settings
2. Click "Settings" → "Domains"
3. Add your custom domain
4. Update DNS records as instructed

### 3. Enable HTTPS
Railway automatically provides HTTPS for all deployments.

## Troubleshooting

### Database Connection Issues
- Verify environment variables are set correctly
- Check MySQL service is running
- Ensure database credentials are correct
- Check if database is accessible from Railway

### Build Failures
- Check Railway build logs
- Verify nixpacks.toml configuration
- Ensure all PHP extensions are listed
- Check for syntax errors in PHP files

### Application Errors
- Check Railway application logs
- Verify file permissions
- Ensure all required files are in repository
- Check PHP error logs

## Monitoring

### Railway Dashboard
- View deployment status
- Monitor resource usage
- Check application logs
- View metrics and analytics

### Database Monitoring
- Monitor database connections
- Check query performance
- View database size and usage
- Set up backups

## Scaling

### Vertical Scaling
- Upgrade Railway plan for more resources
- Increase memory and CPU allocation

### Horizontal Scaling
- Railway supports multiple instances
- Configure load balancing if needed

## Backup Strategy

### Database Backups
1. Use Railway's built-in backup feature
2. Set up automated backups
3. Export database regularly
4. Store backups in secure location

### Code Backups
- GitHub repository serves as code backup
- Tag releases for version control
- Maintain separate branches for production

## Security Considerations

1. **Environment Variables**: Never commit sensitive data
2. **Database Access**: Restrict to Railway network only
3. **HTTPS**: Always use HTTPS (enabled by default)
4. **Updates**: Keep PHP and dependencies updated
5. **Monitoring**: Set up alerts for suspicious activity

## Cost Estimation

Railway pricing (as of 2024):
- **Hobby Plan**: $5/month (includes $5 credit)
- **Pro Plan**: $20/month (includes $20 credit)
- Additional usage charged per resource

Estimated monthly cost for OES:
- Web service: ~$5-10
- MySQL database: ~$5-10
- Total: ~$10-20/month

## Support

- Railway Documentation: https://docs.railway.app
- Railway Discord: https://discord.gg/railway
- GitHub Issues: https://github.com/abre0101/OES_Deploy/issues

## Quick Commands

### View Logs
```bash
railway logs
```

### Connect to Database
```bash
railway connect mysql
```

### Run Migrations
```bash
railway run php migrate.php
```

### Restart Service
```bash
railway restart
```

## Next Steps After Deployment

1. ✅ Test all functionality
2. ✅ Set up monitoring and alerts
3. ✅ Configure custom domain
4. ✅ Set up automated backups
5. ✅ Document any custom configurations
6. ✅ Train users on the system
7. ✅ Set up support channels

---

**Deployment Date**: February 5, 2026
**Version**: 1.0.0
**Repository**: https://github.com/abre0101/OES_Deploy.git
