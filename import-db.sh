#!/bin/bash
# Import database schema to Railway MySQL

echo "Importing oes_professional.sql..."
mysql -h $MYSQL_HOST -P $MYSQL_PORT -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE < database/oes_professional.sql

echo "Importing sample data..."
mysql -h $MYSQL_HOST -P $MYSQL_PORT -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE < database/insert_sample_data.sql

echo "Database import complete!"
