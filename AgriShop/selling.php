<?php
include 'post.php'; // Only for handling form submission

if (!isset($_SESSION['username'])) {
    die("Error: User not logged in.");
}

// Database Connection for user info
$conn_user = new mysqli("localhost", "root", "", "agrishop");
if ($conn_user->connect_error) {
    die("Connection failed: " . $conn_user->connect_error);
}

$user = $_SESSION['username'];

// Get user profile image
$sql_user = "SELECT profile_image FROM users WHERE username = ?";
$stmt_user = $conn_user->prepare($sql_user);
$stmt_user->bind_param("s", $user);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_data = $result_user->fetch_assoc();

// Set default profile image if empty
$default_image = 'uploads/default.png';
$profile_image = !empty($user_data['profile_image']) ? htmlspecialchars($user_data['profile_image']) : $default_image;

$conn_user->close();
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="css/style.css">
   
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
            <a href="index.php" class="navbar-brand">AgriShop: Farm Online Website</a>
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

<div class="post-container">
    <img src="<?php echo $profile_image; ?>" class="profile-pic">
    <div class="post-box" data-toggle="modal" data-target="#postModal">Click to post what you are selling</div>
</div>

<div id="postModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Post Your Item for Sale</h4>
            </div>
            <div class="modal-body">
                <form action="post.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="category">Select Category:</label>
                        <select class="form-control" name="category" required>
                            <option value="selling">Selling</option>
                            <option value="buying">Buying</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea class="form-control" name="description" placeholder="What are you selling?" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="region">Region:</label>
                        <select class="form-control" id="region" name="region" required>
                            <option value="">Select Region</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="province">Province:</label>
                        <select class="form-control" id="province" name="province" required>
                            <option value="">Select Province</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="city">City/Municipality:</label>
                        <select class="form-control" id="city" name="city" required>
                            <option value="">Select City/Municipality</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="file">Upload Image/Video:</label>
                        <input type="file" class="form-control" name="file" accept="image/*,video/*">
                    </div>

                    <button type="submit" class="btn btn-primary" name="submit">Post</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="posts">
    <?php
    // Database Connection for displaying posts
    $conn_display = new mysqli("localhost", "root", "", "agrishop");
    if ($conn_display->connect_error) {
        die("Connection failed: " . $conn_display->connect_error);
    }

    $sql = "SELECT posts.*, users.fullname, users.profile_image, posts.region, posts.province, posts.city FROM posts
                JOIN users ON posts.username = users.username
                WHERE posts.category='selling'
                ORDER BY posts.created_at DESC";
    $result = $conn_display->query($sql);

    while ($row = $result->fetch_assoc()) {
        echo "<div class='post'>";
        echo "<div class='post-header'>";
        echo "<div style='display: flex; align-items: center;'>"; // Container for profile and name
        echo "<img src='" . htmlspecialchars($row['profile_image']) . "' class='profile-pic'>";
        echo "<p class='username' style='margin-left: 10px;'><strong>" . htmlspecialchars($row['fullname']) . "</strong></p>";
        echo "</div>";
        echo "<div style='display: flex; align-items: center;'>"; // Container for time and message icon/dropdown
        echo "<span class='time' data-time='" . $row['created_at'] . "'></span>";
        // If the post belongs to the logged-in user, show the 3-dot menu
if ($row['username'] == $user) {
    echo "<div class='dropdown' style='margin-left: 10px;'>
            <button class='btn btn-default dropdown-toggle' type='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                <i class='fa fa-ellipsis-h'></i>
            </button>
            <ul class='dropdown-menu'>
                <li><a href='#' class='edit-post' data-id='" . $row['id'] . "' data-description='" . htmlspecialchars($row['description']) . "'>Edit</a></li>
                <li>
                    <form method='POST' action='delete_post.php' onsubmit='return confirm(\"Are you sure you want to delete this post?\");'>
                        <input type='hidden' name='post_id' value='" . $row['id'] . "'>
                        <button type='submit' class='dropdown-item' style='background: none; border: none; padding: 8px 15px; color: black; width: 100%; text-align: left;'>Delete</button>
                    </form>
                </li>
            </ul>
        </div>";
} else {
    // If it's not the logged-in user's post, add the message icon
    echo "<a href='message.php?seller_id=" . htmlspecialchars($row['username']) . "' class='message-icon'><i class='fas fa-envelope'></i></a>";
}
        echo "</div>"; // Close time/icon container
        echo "</div>"; // Close post-header

        echo "<p class='description'>" . htmlspecialchars($row['description']) . "</p>";
        echo "<p class='location'>Location: " . htmlspecialchars($row['region']) . ", " . htmlspecialchars($row['province']) . ", " . htmlspecialchars($row['city']) . "</p>";
        if (!empty($row['file_path'])) {
            echo "<img src='" . htmlspecialchars($row['file_path']) . "' class='post-image'>";
        }
        echo "</div>"; // Close post
    }

    $conn_display->close();
    ?>
</div>

<div id="editModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Post</h4>
            </div>
            <div class="modal-body">
                <form method="POST" action="edit_post.php">
                    <input type="hidden" name="post_id" id="edit-post-id">
                    <textarea name="description" id="edit-description" class="form-control" required></textarea>
                    <br>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        // Fix Dropdown Toggle
        $(".dropdown-toggle").click(function(e) {
            e.stopPropagation(); // Prevent immediate closing
            $(this).next(".dropdown-menu").toggle();
        });

        // Close dropdown when clicking outside
        $(document).click(function() {
            $(".dropdown-menu").hide();
        });

        // Open Edit Post Modal
        $(".edit-post").click(function() {
            var postId = $(this).data("id");
            var description = $(this).data("description");
            $("#edit-post-id").val(postId);
            $("#edit-description").val(description);
            $("#editModal").modal("show");
        });

        // Fetch Regions, Provinces, and Cities using AJAX
        function populateDropdown(url, dropdown) {
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    dropdown.empty().append('<option value="">Select ' + dropdown.prop('id').charAt(0).toUpperCase() + dropdown.prop('id').slice(1) + '</option>');
                    $.each(data, function(key, value) {
                        dropdown.append('<option value="' + value + '">' + value + '</option>');
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching " + dropdown.prop('id') + ": " + error);
                }
            });
        }

        populateDropdown('get_regions.php', $('#region'));

        $('#region').change(function() {
            var selectedRegion = $(this).val();
            if (selectedRegion) {
                populateDropdown('get_provinces.php?region=' + selectedRegion, $('#province'));
            } else {
                $('#province').empty().append('<option value="">Select Province</option>');
                $('#city').empty().append('<option value="">Select City/Municipality</option>');
            }
        });

        $('#province').change(function() {
            var selectedProvince = $(this).val();
            if (selectedProvince) {
                populateDropdown('get_cities.php?province=' + selectedProvince, $('#city'));
            } else {
                $('#city').empty().append('<option value="">Select City/Municipality</option>');
            }
        });
    });
</script>
<script>
    // Open Edit Post Modal (Keep this for the edit functionality)
    $(".edit-post").click(function() {
        var postId = $(this).data("id");
        var description = $(this).data("description");
        $("#edit-post-id").val(postId);
        $("#edit-description").val(description);
        $("#editModal").modal("show");
    });
</script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
</body>
</html>