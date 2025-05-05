<?php
session_start();
include "database.php"; // Ensure database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        echo "<script>alert('Both fields are required!'); window.location='index.php';</script>";
        exit();
    }

    $sql = "SELECT * FROM users WHERE username=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        if (password_verify($password, $user['password'])) {
        } else {
            echo "<script>alert('Invalid login credentials'); window.location='adminpage.php';</script>";
        }
            // Store user data in session
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['fullname']; // Store full name
            $_SESSION['profile_pic'] = $user['profile_pic'] ?? 'default.jpg'; // Store profile pic (if applicable)

            // Check if the user is an admin
            if ($user['is_admin'] == 1) {
                $_SESSION['is_admin'] = true;
                header("Location: adminpage.php"); // Redirect to the admin page
                exit();
            } else {
                $_SESSION['is_admin'] = false;
                header("Location: mainhome.php"); // Redirect to the user home page
                exit();
            }
        } else {
            echo "<script>alert('Invalid login credentials'); window.location='index.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('User not found!'); window.location='index.php';</script>";
        exit();
    }


$conn->close();
?>
