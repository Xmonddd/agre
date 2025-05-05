<?php
session_start();

// ✅ Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['is_admin'] != true) {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "agrishop");

// ✅ Check DB connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_SESSION['username'] ?? null;

// ✅ Fetch profile info if not already set
if ($username && (!isset($_SESSION['profile_image']) || !isset($_SESSION['fullname']))) {
    $stmt = $conn->prepare("SELECT fullname, profile_image FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['profile_image'] = $user['profile_image'] ?? 'uploads/default.png';
    }
    $stmt->close();
}

// ✅ Assign fallback values
$profile_from_session = $_SESSION['profile_image'] ?? 'uploads/default.png';
$user_profile_image = (strpos($profile_from_session, 'uploads/') === false)
    ? 'uploads/' . $profile_from_session
    : $profile_from_session;

$user_fullname = $_SESSION['fullname'] ?? 'Guest User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>AgriShop: Farm Online Website</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- CSS -->
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-default">
    <div class="container-fluid">
        <!-- Brand and toggle -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a href="index.php" class="navbar-brand">AgriShop: Farm Online Website</a>
        </div>

        <!-- Navbar links -->
        <div class="collapse navbar-collapse" id="navbar-collapse">
            <ul class="nav navbar-nav navbar-right">
                <li><a href=""><i class="fas fa-home"></i> HOME</a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <img src="<?php echo htmlspecialchars($user_profile_image); ?>" class="profile-pic-nav" alt="Profile"> <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="profile.php">My Profile</a></li>
                        <li class="divider"></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Page content can go here -->

<!-- Footer -->
<?php include("footer.php"); ?>

</body>
</html>
