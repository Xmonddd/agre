<?php
// Database Connection (replace with your actual credentials)
$conn = new mysqli("localhost", "root", "", "agrishop");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$region = $_GET['region'];
$provinces = array();
// Replace this with your actual logic to fetch provinces based on the selected region
$stmt = $conn->prepare("SELECT DISTINCT province FROM locations WHERE region = ? ORDER BY province ASC");
$stmt->bind_param("s", $region);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $provinces[] = $row['province'];
    }
}
$stmt->close();
$conn->close();
header('Content-Type: application/json');
echo json_encode($provinces);
?>