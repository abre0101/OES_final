<?php
// Update password for alem.h
require_once('utils/password_helper.php');
require_once('Connections/OES.php');

$username = 'alem.h';
$newPassword = 'password1';

// Hash the password
$hashedPassword = hashPassword($newPassword);

// Update in database
$stmt = $con->prepare("UPDATE students SET password = ? WHERE username = ?");
$stmt->bind_param("ss", $hashedPassword, $username);

if ($stmt->execute()) {
    echo "Password updated successfully for user: $username\n";
    echo "New password: $newPassword\n";
    echo "You can now login with this password.\n";
} else {
    echo "Error updating password: " . $stmt->error . "\n";
}

$stmt->close();
$con->close();
?>
