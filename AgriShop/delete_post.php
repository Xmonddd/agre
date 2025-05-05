<?php
session_start();
$conn = new mysqli("localhost", "root", "", "agrishop");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["post_id"])) {
    $post_id = $_POST["post_id"];

    $sql = "DELETE FROM posts WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);

    if ($stmt->execute()) {
        $previous_page = $_SERVER['HTTP_REFERER'] ?? 'index.php'; // Default page kung walang referrer
        header("Location: $previous_page");
        exit();
    }
    
}
?>
