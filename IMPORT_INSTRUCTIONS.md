# Database Import Instructions for Railway

Your app is deployed but the database needs to be imported. The internal network connection isn't working, so use the Railway dashboard instead.

## Option 1: Import via Railway Dashboard (Recommended)

1. Go to your Railway project: https://railway.app/project/ab828ecc-51a1-4382-b193-67ac6c864b77

2. Click on the **MySQL** service

3. Click on the **Data** tab

4. Click **Query** button

5. Open `database/oes_professional.sql` in a text editor

6. Copy ALL the contents and paste into the Query window

7. Click **Run** or **Execute**

8. Wait for it to complete (may take a minute)

9. Repeat steps 5-8 for `database/insert_sample_data.sql`

## Option 2: Use MySQL Workbench or phpMyAdmin

### Connection Details:
- **Host:** yamanote.proxy.rlwy.net
- **Port:** 25317
- **Username:** root
- **Password:** WVfbKCqYyoVFszxfuaEgmGkTdSkxaLWk
- **Database:** railway

### Steps:
1. Open MySQL Workbench
2. Create new connection with the details above
3. Connect to the database
4. Go to File → Run SQL Script
5. Select `database/oes_professional.sql`
6. Execute
7. Repeat for `database/insert_sample_data.sql`

## Option 3: Use MySQL Command Line (if installed)

```cmd
mysql -h yamanote.proxy.rlwy.net -P 25317 -u root -pWVfbKCqYyoVFszxfuaEgmGkTdSkxaLWk railway < database/oes_professional.sql

mysql -h yamanote.proxy.rlwy.net -P 25317 -u root -pWVfbKCqYyoVFszxfuaEgmGkTdSkxaLWk railway < database/insert_sample_data.sql
```

## After Import

Once the database is imported, test your application:

1. Visit: https://web-production-08e8e.up.railway.app
2. Test database connection: https://web-production-08e8e.up.railway.app/test-db.php
3. Try logging in with sample credentials from your database

## Your Deployment URLs

- **Live App:** https://web-production-08e8e.up.railway.app
- **Railway Dashboard:** https://railway.app/project/ab828ecc-51a1-4382-b193-67ac6c864b77
