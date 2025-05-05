<?php
session_start();
$conn = new mysqli("localhost", "root", "", "agrishop");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure user is logged in
if (!isset($_SESSION['fullname'])) {
    die("User not logged in.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["send_message"])) {
    $sender = $_SESSION['fullname']; // Naka-login na user
    $receiver = $_POST['receiver'];
    $message = $_POST['message'];


       // ✅ 1. Check if the receiver exists in the `users` table
       $checkUser = $conn->prepare("SELECT fullname FROM users WHERE fullname = ?");
       $checkUser->bind_param("s", $receiver);
       $checkUser->execute();
       $result = $checkUser->get_result();
   
       if ($result->num_rows === 0) {
           die("Error: Receiver does not exist."); // Stop execution if receiver is invalid
       }
    // Iwasan ang SQL injection
    $stmt = $conn->prepare("INSERT INTO messages (sender, receiver, message, timestamp) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $sender, $receiver, $message);

    if ($stmt->execute()) {
        header("Location: message.php?user=$receiver"); // Ibalik sa message page
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}

$conn->close();
?>
