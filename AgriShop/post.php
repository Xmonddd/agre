<?php

// Check if a session is already started
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start session if not already started
}


if (!isset($_SESSION['username'])) {
    die("Error: User not logged in.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" &&
    isset($_POST['description'], $_POST['category'], $_POST['region'], $_POST['province'], $_POST['city'])) {
    $description = $_POST['description'];
    $category = $_POST['category'];
    $region = $_POST['region'];
    $province = $_POST['province'];
    $city = $_POST['city'];
    $username = $_SESSION['username'];
    $file_path = ''; // Initialize file path

    // Handle file upload (similar to your existing upload.php)
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $fileName = basename($_FILES['file']['name']);
        $targetFilePath = $uploadDir . $fileName;

        // Move uploaded file
        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFilePath)) {
            $file_path = $targetFilePath;
        } else {
            echo "Error uploading file.";
            exit;
        }
    }

    // Database Connection
    $conn = new mysqli("localhost", "root", "", "agrishop");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "INSERT INTO posts (username, description, category, file_path, region, province, city, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $username, $description, $category, $file_path, $region, $province, $city);

    if ($stmt->execute()) {
        if ($category === 'selling') {
            header("Location: selling.php"); // Redirect back to the selling page
        } elseif ($category === 'buying') {
            header("Location: buying.php"); // Redirect back to the buying page
        }
        exit;
    } else {
        echo "Error creating post: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
} else {
    // Handle cases where the form is not submitted via POST
    // You might want to display a message or redirect to a relevant page
}
?>