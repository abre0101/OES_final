<?php
// Database Connection Test Page
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #003366; }
        .success { color: #28a745; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0; }
        .info { color: #0c5460; padding: 10px; background: #d1ecf1; border-radius: 5px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #003366; color: white; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Database Connection Test</h1>
        
        <h2>Environment Variables</h2>
        <table>
            <tr>
                <th>Variable</th>
                <th>Value</th>
                <th>Status</th>
            </tr>
            <?php
            $env_vars = [
                'MYSQL_HOST' => getenv('MYSQL_HOST') ?: getenv('MYSQLHOST'),
                'MYSQL_PORT' => getenv('MYSQL_PORT') ?: getenv('MYSQLPORT'),
                'MYSQL_DATABASE' => getenv('MYSQL_DATABASE') ?: getenv('MYSQLDATABASE'),
                'MYSQL_USER' => getenv('MYSQL_USER') ?: getenv('MYSQLUSER'),
                'MYSQL_PASSWORD' => (getenv('MYSQL_PASSWORD') ?: getenv('MYSQLPASSWORD')) ? '***SET***' : null
            ];
            
            foreach ($env_vars as $key => $value) {
                $status = $value ? '✅ Set' : '❌ Not Set';
                $display_value = $value ?: '<em>Not set</em>';
                echo "<tr><td><code>$key</code></td><td>$display_value</td><td>$status</td></tr>";
            }
            ?>
        </table>
        
        <h2>Connection Test</h2>
        <?php
        $hostname = getenv('MYSQL_HOST') ?: getenv('MYSQLHOST') ?: 'localhost';
        $database = getenv('MYSQL_DATABASE') ?: getenv('MYSQLDATABASE') ?: 'oes_professional';
        $username = getenv('MYSQL_USER') ?: getenv('MYSQLUSER') ?: 'root';
        $password = getenv('MYSQL_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: '';
        $port = getenv('MYSQL_PORT') ?: getenv('MYSQLPORT') ?: 3306;
        
        echo "<div class='info'>";
        echo "<strong>Attempting to connect to:</strong><br>";
        echo "Host: <code>$hostname</code><br>";
        echo "Port: <code>$port</code><br>";
        echo "Database: <code>$database</code><br>";
        echo "User: <code>$username</code>";
        echo "</div>";
        
        try {
            $con = new mysqli($hostname, $username, $password, $database, $port);
            
            if ($con->connect_error) {
                throw new Exception($con->connect_error);
            }
            
            echo "<div class='success'>";
            echo "<strong>✅ Database Connected Successfully!</strong><br>";
            echo "MySQL Version: " . $con->server_info . "<br>";
            echo "Character Set: " . $con->character_set_name();
            echo "</div>";
            
            // Test query
            $result = $con->query("SHOW TABLES");
            if ($result) {
                echo "<h3>Database Tables</h3>";
                if ($result->num_rows > 0) {
                    echo "<table><tr><th>Table Name</th></tr>";
                    while ($row = $result->fetch_array()) {
                        echo "<tr><td>" . $row[0] . "</td></tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<div class='info'>No tables found in database. You need to import your schema.</div>";
                }
            }
            
            $con->close();
            
        } catch (Exception $e) {
            echo "<div class='error'>";
            echo "<strong>❌ Database Connection Failed!</strong><br>";
            echo "Error: " . htmlspecialchars($e->getMessage()) . "<br><br>";
            echo "<strong>Possible Solutions:</strong><br>";
            echo "1. Add MySQL database in Railway (+ New → Database → Add MySQL)<br>";
            echo "2. Verify environment variables are set correctly<br>";
            echo "3. Check if services are linked in Railway<br>";
            echo "4. Restart the deployment after adding MySQL";
            echo "</div>";
        }
        ?>
        
        <h2>PHP Information</h2>
        <div class='info'>
            <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?><br>
            <strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?><br>
            <strong>Environment:</strong> <?php echo getenv('RAILWAY_ENVIRONMENT') ?: 'Local'; ?>
        </div>
        
        <p style="margin-top: 30px; text-align: center; color: #666;">
            <a href="/" style="color: #003366;">← Back to Home</a> | 
            <a href="/health.php" style="color: #003366;">Health Check</a>
        </p>
    </div>
</body>
</html>
