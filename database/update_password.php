<?php
// Update password user TAS, TRI, TROA
require_once '../config/database.php';

echo "<h2>Update Password User</h2>";
echo "<hr>";

// Update password TAS
$sql1 = "UPDATE users SET password = 'tas123' WHERE username = 'tas'";
if(mysqli_query($conn, $sql1)) {
    echo "<p style='color:green'>✓ Password TAS diupdate ke: tas123</p>";
} else {
    echo "<p style='color:red'>✗ Error: " . mysqli_error($conn) . "</p>";
}

// Update password TRI
$sql2 = "UPDATE users SET password = 'tri123' WHERE username = 'tri'";
if(mysqli_query($conn, $sql2)) {
    echo "<p style='color:green'>✓ Password TRI diupdate ke: tri123</p>";
} else {
    echo "<p style='color:red'>✗ Error: " . mysqli_error($conn) . "</p>";
}

// Update password TROA
$sql3 = "UPDATE users SET password = 'troa123' WHERE username = 'troa'";
if(mysqli_query($conn, $sql3)) {
    echo "<p style='color:green'>✓ Password TROA diupdate ke: troa123</p>";
} else {
    echo "<p style='color:red'>✗ Error: " . mysqli_error($conn) . "</p>";
}

echo "<hr>";
echo "<p>Update password selesai! <a href='../index.php'>Ke Halaman Login</a></p>";

// Tampilkan data user
$result = mysqli_query($conn, "SELECT username, password FROM users WHERE role = 'operator'");
echo "<h3>Data User Operator:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr style='background:#f0f0f0'><th>Username</th><th>Password</th></tr>";
while($row = mysqli_fetch_assoc($result)) {
    echo "<tr><td>" . $row['username'] . "</td><td>" . $row['password'] . "</td></tr>";
}
echo "</table>";
?>
