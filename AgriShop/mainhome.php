<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Only start the session if it's not already started
}

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php"); // Redirect to login if not logged in
    exit();
}

include 'post.php';

// Database Connection (for both SRP and main posts)
$conn = new mysqli("localhost", "root", "", "agrishop");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$username = $_SESSION['username'] ?? null;

// ✅ Fetch profile info from DB if not already set
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

// ✅ Fallback if still not set
$user_fullname = $_SESSION['fullname'] ?? 'Guest User';
$profile_from_session = $_SESSION['profile_image'] ?? 'uploads/default.png';

// ✅ Ensure path includes "uploads/"
$user_profile_image = (strpos($profile_from_session, 'uploads/') === false)
    ? 'uploads/' . $profile_from_session
    : $profile_from_session;


// Pagination settings for SRP
$srp_items_per_page = 15;
$srp_current_page = isset($_GET['srp_page']) ? intval($_GET['srp_page']) : 1;
$srp_offset = ($srp_current_page - 1) * $srp_items_per_page;

// Fetch total number of SRP prices
$sql_total_srp = "SELECT COUNT(*) AS total FROM srp_prices";
$result_total_srp = $conn->query($sql_total_srp);
$total_srp_rows = $result_total_srp->fetch_assoc()['total'];
$total_srp_pages = ceil($total_srp_rows / $srp_items_per_page);

// Fetch SRP prices for the current page
$sql_srp = "SELECT crop_name, min_price, max_price FROM srp_prices LIMIT $srp_offset, $srp_items_per_page";
$result_srp = $conn->query($sql_srp);
$srp_prices = [];
if ($result_srp->num_rows > 0) {
    while ($row = $result_srp->fetch_assoc()) {
        $srp_prices[] = $row;
    }
}

// Handle new main post submission and database insertion
if (isset($_POST['session_post_description']) && isset($_FILES['session_post_image'])) {
    $description = $_POST['session_post_description'];
    $image = $_FILES['session_post_image'];
    $username = $_SESSION['username'] ?? null; // Get username from session

    $upload_dir = 'uploads/'; // Directory to save images
    $image_name = basename($image['name']);
    $upload_file = $upload_dir . $image_name;

    if (move_uploaded_file($image['tmp_name'], $upload_file)) {
        $sql_insert_post = "INSERT INTO mainpost (username, description, file_path, created_at)
                                    VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql_insert_post);
        $stmt->bind_param("sss", $username, $description, $upload_file);

        if ($stmt->execute()) {
            // Post inserted successfully
            header("Location: mainhome.php");
            exit();
        } else {
            echo "Error saving post to database: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error uploading image.";
    }
}

// Fetch main posts from the database for display
$sql_fetch_posts = "SELECT mp.*, u.fullname, u.profile_image
                                FROM mainpost mp
                                INNER JOIN users u ON mp.username = u.username
                                ORDER BY mp.created_at DESC";
$result_posts = $conn->query($sql_fetch_posts);
$main_posts = [];
if ($result_posts->num_rows > 0) {
    while ($row = $result_posts->fetch_assoc()) {
        $main_posts[] = $row;
    }
}


// Fetch user data (for profile display in the "Click to post" area and dropdown)
$user_fullname = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Guest User';
$user_profile_image = isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : 'uploads/default.png';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>AgriShop: Farm Online Website</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/stylemainhome.css">
    <style>
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

<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a href="mainhome.php" class="navbar-brand">AgriShop: Farm Online Website</a>
        </div>

        <div class="collapse navbar-collapse" id="navbar-collapse">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="srp.php"><i class="fas fa-home"></i> HOME</a></li>
                <li><a href="buying.php"><i class="fas fa-shopping-cart"></i> BUYING</a></li>
                <li><a href="selling.php"><i class="fas fa-tag"></i> SELLING</a></li>
                <li><a href="mainhome.php"><i class="fas fa-home"></i> TRANSACT</a></li>
                <li><a href="message.php"><i class="fas fa-envelope fa-2x"></i></a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <img src="<?php echo htmlspecialchars($user_profile_image); ?>" alt="Profile" class="profile-pic-nav"> <span class="caret"></span>
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

<div class="content-wrapper">
    <div class="srp-container">
        <br><br><h2>Suggested Retail Prices</h2>
        <?php if (!empty($srp_prices)): ?>
            <table class="srp-table">
                <thead>
                    <tr>
                        <th>Crop</th>
                        <th>Min Price</th>
                        <th>Max Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($srp_prices as $price): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($price['crop_name']); ?></td>
                            <td>PHP <?php echo htmlspecialchars(number_format($price['min_price'], 2)); ?></td>
                            <td>PHP <?php echo htmlspecialchars(number_format($price['max_price'], 2)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="srp-pagination">
                <?php if ($srp_current_page > 1): ?>
                    <a href="?srp_page=<?php echo $srp_current_page - 1; ?>">Back</a>
                <?php else: ?>
                    <span class="disabled">Back</span>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_srp_pages; $i++): ?>
                    <?php if ($i == $srp_current_page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?srp_page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($srp_current_page < $total_srp_pages): ?>
                    <a href="?srp_page=<?php echo $srp_current_page + 1; ?>">Next</a>
                <?php else: ?>
                    <span class="disabled">Next</span>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <p>No SRP prices available.</p>
        <?php endif; ?>
    </div>

    <div class="main-content">
        <div class="feed-container">
            <div class="post-creation-container" data-toggle="modal" data-target="#postModal">
                <div class="profile-info-inline">
                    <img src="<?php echo htmlspecialchars($user_profile_image); ?>" alt="Profile Picture">
                </div>
                <div class="post-placeholder">Click to post your success transaction</div>
            </div>

            <div class="post-display-container">
                <div class="posts">
                    <?php if (!empty($main_posts)): ?>
                        <?php foreach ($main_posts as $post): ?>
                            <div class="post">
                                <div class="post-header">
                                    <div class="post-header-left">
                                        <img src="<?php echo htmlspecialchars($post['profile_image']); ?>" alt="User Profile">
                                        <span class="username"><?php echo htmlspecialchars($post['fullname']); ?></span>
                                    </div>
                                    <span class="time"><?php echo date('F j, Y, g:i a', strtotime($post['created_at'])); ?></span>
                                </div>
                                <div class="post-content"><?php echo htmlspecialchars($post['description']); ?></div>
                                <?php if (!empty($post['file_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($post['file_path']); ?>" class="post-image">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #777;">No posts yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="postModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Create a Post</h4>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="profile-info">
                            <img src="<?php echo htmlspecialchars($user_profile_image); ?>" alt="ProfilePicture">
                            <strong><?php echo htmlspecialchars($user_fullname); ?></strong>
                        </div>
                        <textarea name="session_post_description" placeholder="What's on your mind?" required></textarea>
                        <input type="file" name="session_post_image" accept="image/*" required>
                        <button type="submit" class="btn btn-primary">Post</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function(){
        // No specific JavaScript needed for this implementation as Bootstrap handles the modal
    });
</script>
</body>
</html>
<?php
// Close database connection
$conn->close();
?>