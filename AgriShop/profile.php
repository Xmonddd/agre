<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "agrishop");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_SESSION['username'];

// Fetch user profile information
$sql_user = "SELECT fullname, profile_image FROM users WHERE username = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $username);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_data = $result_user->fetch_assoc();

$fullname = $user_data['fullname'];
$profile_image = !empty($user_data['profile_image']) ? htmlspecialchars($user_data['profile_image']) : 'uploads/default.png';

// Fetch user's posts
$sql_posts = "SELECT posts.*, posts.region, posts.province, posts.city FROM posts WHERE username = ? ORDER BY created_at DESC";
$stmt_posts = $conn->prepare($sql_posts);
$stmt_posts->bind_param("s", $username);
$stmt_posts->execute();
$result_posts = $stmt_posts->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Profile - AgriShop</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* post display */
        .post-button-container {
    flex: 1;
            background: #7e5e74;
            padding: 10px 15px;
            border-radius: 8px;
            color: black;
            text-align: left;
            cursor: pointer;
            height: 40px;
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            width: 80%;
            max-width: 600px;
}

.post-button-container h3 {
    margin: 0;
    color: #000000;
}
.post-creation-container {
    background-color: rgb(131, 131, 136);
    border: 1px solid #ddd;
    padding: 10px;
    margin-top: 20px;
    margin-bottom: 1px;
    margin-left: auto; /* Center horizontally */
    margin-right: auto; /* Center horizontally */
    border-radius: 5px;
    cursor: pointer;
    width: 80%;
    max-width: 600px;
    display: flex; /* Align profile info and placeholder */
    align-items: center;
}

.profile-info-inline {
    display: flex;
    align-items: center;
    margin-right: 10px;
}

.profile-info-inline img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 8px;
    object-fit: cover;
}

.post-placeholder {
    flex-grow: 1;
    background-color: #ffffff; /* Light gray background like Facebook */
    border-radius: 20px; /* Rounded corners like Facebook */
    padding: 10px 15px;
    color: #000000; /* Gray placeholder text */
    font-size: 16px;
}

.post-placeholder:hover {
    background-color: #acaeb1; /* Slightly darker on hover */
}
#postModal {
    display: none; /* Hide the modal by default */
}

#postModal .modal-dialog {
    margin-top: 100px; /* Adjust vertical position */
}

#postModal .modal-content {
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
}

#postModal .modal-header {
    border-bottom: 1px solid #eee;
    padding-bottom: 15px;
    margin-bottom: 15px;
    color: #333; /* Darker text */
}

#postModal .modal-header h4 {
    margin: 0;
}

#postModal .profile-info {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

#postModal .profile-info img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 10px;
    object-fit: cover;
}

#postModal .profile-info strong {
    font-size: 1.1em;
    color: #333; /* Darker text */
}

#postModal textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    resize: vertical;
    color: #333; /* Darker text */
}

#postModal input[type="file"] {
    width: 100%;
    margin-bottom: 10px;
    color: #333; /* Darker text */
}

#postModal button[type="submit"] {
    padding: 10px 15px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1em;
}

#postModal button[type="submit"]:hover {
    background-color: #0056b3;
}

.post-display-container {
    width: 100%;
    max-width: 1000px;
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 20px; /* Add some space above the posts */
}

.posts {
    display: flex;
    flex-direction: column;
    align-items: center; /* Center the posts horizontally */
    width: 100%;
    max-width: 600px; /* Limit the width of the post container */
    margin-bottom: 1px; /* Add spacing between the post container and other elements */
    margin-left: auto; /* Center horizontally */
    margin-right: auto; /* Center horizontally */
}

.post {
    width: 100%;
    background: #575454;
    padding: 20px;
    border-radius: 8px;
    text-align: left; /* Align text to the left inside the post */
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
    margin-bottom: 20px;
    min-height: auto; /* Adjust min-height as needed */
    color: white; /* Text color */
}
/* === POST HEADER === */
.post-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 16px; /* Reduced font size for header */
    color: white;
    margin-bottom: 10px; /* Space between header and content */
}

.post-header-left {
    display: flex;
    align-items: center;
}

.post-header img {
    width: 30px; /* Smaller profile image */
    height: 30px;
    border-radius: 50%;
    margin-right: 10px;
    object-fit: cover;
}

/* === USERNAME & TIME === */
.username {
    font-weight: bold;
    color: white;
}

.time {
    font-size: 0.8em;
    color: #ccc;
}

/* === POST CONTENT (DESCRIPTION) === */
.post-content {
    color: white;
    margin-bottom: 10px;
    white-space: pre-line; /* Preserve line breaks */
}

/* === POST IMAGE === */
.post-image {
    width: 100%;
    max-width: 500px;
    height: auto;
    border-radius: 5px;
    margin-top: 10px;
    display: block; /* Prevent extra space below image */
}
        /* profile */
        .profile-container {
            padding: 20px;
            margin-top: 20px; /* Adjust based on navbar height */
            text-align: center;
        }

        .profile-pic-large {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 5px solid #f8f9fa;
        }

        .profile-info {
            margin-bottom: 20px;
        }

        .profile-info h2 {
            margin-top: 0;
            color: #333;
        }

        .profile-posts-title {
            margin-top: 30px;
            margin-bottom: 15px;
            color: #555;
            text-align: center; /* Center the title */
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        /* Navbar CSS (Adopted) */
        .navbar-nav > li > .dropdown-menu {
            margin-top: 0; /* Remove the gap */
        }
        .dropdown-menu > li > a {
            padding: 8px 15px;
        }
        .dropdown-toggle::after {
            content: none; /* Remove the default dropdown arrow */
        }
        .navbar-nav > li > .dropdown-toggle {
            padding-right: 15px; /* Adjust padding if needed */
            padding-left: 15px; /* Adjust padding if needed */
            padding-top: 8px; /* Vertically align the image */
            padding-bottom: 8px; /* Vertically align the image */
        }
        .profile-pic-nav {
            width: 30px; /* Adjust to your desired width */
            height: 30px; /* Adjust to your desired height */
            border-radius: 50%; /* Make it circular (optional) */
            object-fit: cover; /* Prevent distortion */
            margin-right: 5px; /* Add some spacing to the right of the image */
            vertical-align: middle; /* Align the image with the text */
        }
    </style>
</head>
<body>

<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a href="index.php" class="navbar-brand">AgriShop: Farm Online Website</a>
        </div>

        <div class="collapse navbar-collapse" id="navbar-collapse">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="mainhome.php"><i class="fas fa-home"></i> HOME</a></li>
                <li><a href="buying.php"><i class="fas fa-shopping-cart"></i> BUYING</a></li>
                <li><a href="selling.php"><i class="fas fa-tag"></i> SELLING</a></li>
                <li><a href="message.php"><i class="fas fa-envelope fa-2x"></i></a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <img src="<?php echo $profile_image; ?>" alt="Profile" class="profile-pic-nav"> <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                        <li role="separator" class="divider"></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="profile-container">
    <img src="<?php echo $profile_image; ?>" alt="Your Profile Picture" class="profile-pic-large">
    <div class="profile-info">
        <h2><?php echo htmlspecialchars($fullname); ?></h2>
        <p>Username: <?php echo htmlspecialchars($username); ?></p>
        </div>

    <h3 class="profile-posts-title">Your Recent Posts:</h3>
    <div class="posts">
        <?php
        if ($result_posts->num_rows > 0) {
            while ($row = $result_posts->fetch_assoc()) {
                echo "<div class='post'>";
                echo "<div class='post-header'>";
                echo "<div>";
                echo "<img src='" . $profile_image . "' alt='" . htmlspecialchars($fullname) . "' class='profile-pic-mini'>";
                echo "<strong>" . htmlspecialchars($fullname) . "</strong>";
                echo "</div>";
                echo "<span class='time'>" . date('F j, Y, g:i a', strtotime($row['created_at'])) . "</span>";
                echo "</div>";
                echo "<p class='description'>" . htmlspecialchars($row['description']) . "</p>";
                echo "<p class='location'>Location: " . htmlspecialchars($row['region']) . ", " . htmlspecialchars($row['province']) . ", " . htmlspecialchars($row['city']) . "</p>";
                if (!empty($row['file_path'])) {
                    echo "<img src='" . htmlspecialchars($row['file_path']) . "' class='post-image'>";
                }
                echo "</div>";
            }
        } else {
            echo "<p>You haven't made any posts yet.</p>";
        }
        ?>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
</body>
</html>