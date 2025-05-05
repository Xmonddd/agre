<?php
// Database Connection (replace with your actual credentials)
$conn = new mysqli("localhost", "root", "", "agrishop");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$province = $_GET['province'];
$cities = array();
// Replace this with your actual logic to fetch cities based on the selected province
$stmt = $conn->prepare("SELECT DISTINCT city FROM locations WHERE province = ? ORDER BY city ASC");
$stmt->bind_param("s", $province);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cities[] = $row['city'];
    }
}
$stmt->close();
$conn->close();
header('Content-Type: application/json');
echo json_encode($cities);
?>