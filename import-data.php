<?php
// Import database schema and sample data
$con = require_once(__DIR__ . "/Connections/OES.php");

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

echo "<h2>Database Import</h2>";

// Read and execute schema
$schema_file = __DIR__ . '/database/oes_professional.sql';
if (file_exists($schema_file)) {
    $schema_sql = file_get_contents($schema_file);
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $schema_sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && substr($statement, 0, 2) !== '--') {
            if ($con->query($statement) === TRUE) {
                echo "<p style='color: green;'>✓ Executed statement</p>";
            } else {
                echo "<p style='color: red;'>✗ Error: " . $con->error . "</p>";
            }
        }
    }
    echo "<h3>Schema imported!</h3>";
} else {
    echo "<p style='color: red;'>Schema file not found!</p>";
}

// Read and execute sample data
$data_file = __DIR__ . '/database/insert_sample_data.sql';
if (file_exists($data_file)) {
    $data_sql = file_get_contents($data_file);
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $data_sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && substr($statement, 0, 2) !== '--') {
            if ($con->multi_query($statement)) {
                do {
                    if ($result = $con->store_result()) {
                        $result->free();
                    }
                } while ($con->next_result());
                echo "<p style='color: green;'>✓ Inserted data</p>";
            } else {
                echo "<p style='color: orange;'>⚠ " . $con->error . "</p>";
            }
        }
    }
    echo "<h3>Sample data imported!</h3>";
} else {
    echo "<p style='color: red;'>Data file not found!</p>";
}

$con->close();

echo "<p><a href='Admin/index.php'>Go to Admin Dashboard</a></p>";
?>
