<?php
// Database Connection (replace with your actual credentials)
$conn = new mysqli("localhost", "root", "", "agrishop");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$regions = array();
// Replace this with your actual logic to fetch regions from your database or a data source
$result = $conn->query("SELECT DISTINCT region FROM locations ORDER BY region ASC");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $regions[] = $row['region'];
    }
}

$conn->close();
header('Content-Type: application/json');
echo json_encode($regions);
?>