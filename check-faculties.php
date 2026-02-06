<?php
// Check and display faculties in database
$con = require_once(__DIR__ . "/Connections/OES.php");

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

echo "<h2>Faculties in Database:</h2>";

$result = $con->query("SELECT * FROM faculties");

if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; padding: 10px;'>";
    echo "<tr><th>Faculty ID</th><th>Faculty Code</th><th>Faculty Name</th><th>Active</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['faculty_id'] . "</td>";
        echo "<td>" . $row['faculty_code'] . "</td>";
        echo "<td>" . $row['faculty_name'] . "</td>";
        echo "<td>" . ($row['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>No faculties found! You need to insert faculties first.</p>";
    echo "<p>Run this SQL to add sample faculties:</p>";
    echo "<pre>";
    echo "INSERT INTO faculties (faculty_code, faculty_name, description, is_active) VALUES\n";
    echo "('HEALTH', 'College of Health Sciences', 'Health and Medical Sciences', 1),\n";
    echo "('ENG', 'College of Engineering', 'Engineering and Technology', 1),\n";
    echo "('BUS', 'College of Business', 'Business and Economics', 1);\n";
    echo "</pre>";
}

$con->close();
?>
