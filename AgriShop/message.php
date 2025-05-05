<?php
session_start();
include_once 'functions.php';
 // Functions are already in this file

// Ensure user is logged in
if (!isset($_SESSION['fullname'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agrishop";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$activeUser = $_SESSION['fullname']; // Now $activeUser is set
//eto yung nilagay ko pang debug
$sql = "SELECT * FROM messages WHERE sender = ? OR receiver = ? ORDER BY timestamp DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $activeUser, $activeUser);
$stmt->execute();
$result = $stmt->get_result();



// Fetch conversations using the function from functions.php
$conversations = getConversations($conn, $activeUser);


// Function to fetch messages between two users
function getMessages($conn, $user1, $user2) {
    $sql = "SELECT messages.*, users.profile_image
                FROM messages
                JOIN users ON messages.sender = users.fullname
                WHERE (messages.sender = ? AND messages.receiver = ?)
                    OR (messages.sender = ? AND messages.receiver = ?)
                ORDER BY messages.timestamp ASC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param("ssss", $user1, $user2, $user2, $user1);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    return $messages;
}

// Function to send a message
function sendMessage($conn, $sender, $receiver, $message) {
    $sql = "INSERT INTO messages (sender, receiver, message, timestamp)
                VALUES (?, ?, ?, CURRENT_TIMESTAMP)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param("sss", $sender, $receiver, $message);
    $stmt->execute();
}

// Function to delete a message
function deleteMessage($conn, $messageId, $activeUser) {
    $sql = "DELETE FROM messages WHERE id = ? AND sender = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param("is", $messageId, $activeUser);
    $stmt->execute();
}



// Fetch conversations **after defining the function**
$conversations = getConversations($conn, $activeUser);








// Handle sending a message
if (isset($_POST['send_message'])) {
    $receiver = $_POST['receiver'];
    $message = $_POST['message'];
    sendMessage($conn, $activeUser, $receiver, $message);
}

// Handle deleting a message
if (isset($_POST['delete_message'])) {
    $messageId = $_POST['message_id'];
    deleteMessage($conn, $messageId, $activeUser);
}

// Get the user to message (if set)
$receiverToMessage = isset($_GET['user']) ? $_GET['user'] : null;


if ($receiverToMessage) {
    $sql = "UPDATE messages SET is_read = 1
                WHERE sender = ? AND receiver = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $receiverToMessage, $activeUser);
    $stmt->execute();
}


// Get messages for display
$messages = [];
if ($receiverToMessage) {
    $messages = getMessages($conn, $activeUser, $receiverToMessage);
}

// Get conversations
$conversations = getConversations($conn, $activeUser);

// Fetch user data for navbar profile image
$default_image = 'uploads/default.png';
$profile_image = !empty($user_data['profile_image']) ? htmlspecialchars($user_data['profile_image']) : $default_image;


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Messaging</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/stylemessage.css">
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

 <div class="messenger-container" >
<div class="conversations">
    <button id="newMessageBtn" class="btn btn-primary btn-sm" style="margin-bottom: 10px;">New Message</button>

    <?php foreach ($conversations as $conversation): ?>

        <div class="conversation-item" onclick="window.location.href='message.php?user=<?php echo urlencode($conversation['fullname']); ?>'">
            <img src="<?php echo htmlspecialchars($conversation['profile_image']); ?>" class="profile-pic">
            <p><?php echo htmlspecialchars($conversation['fullname']); ?></p>


            <?php if ($conversation['unread_count'] > 0): ?>
                <span class="unread-badge"><?php echo $conversation['unread_count']; ?></span>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    </div>
 <div class="messages">
    <?php if ($receiverToMessage): ?>
        <h2>Conversation with <?php echo htmlspecialchars($receiverToMessage); ?></h2>

        <?php if (!empty($messages)): ?>
            <?php
            $lastDate = null;
            $lastTimestamp = null;
            foreach ($messages as $message):
                $messageDate = date('F j, Y', strtotime($message['timestamp']));
                $messageTime = date('h:i A', strtotime($message['timestamp']));

                // Maglagay ng date separator kung iba na ang date
                if ($messageDate !== $lastDate): ?>
                    <div class="timestamp-divider"><?php echo $messageDate; ?></div>
                    <?php $lastDate = $messageDate; ?>
                <?php endif; ?>

                <?php
                if ($lastTimestamp && (strtotime($message['timestamp']) - strtotime($lastTimestamp)) > 300): ?>
                    <div class="time-divider"><?php echo $messageTime; ?></div>
                <?php endif; ?>

                <div class="message <?php echo ($message['sender'] == $activeUser) ? 'sent' : 'received'; ?>">

                    <?php if ($message['sender'] != $activeUser): ?>
                        <img src="<?php echo htmlspecialchars($message['profile_image']); ?>" class="profile-pic">
                    <?php endif; ?>

                    <div class="message-content">
                        <span><?php echo htmlspecialchars($message['message']); ?></span>
                    </div>

                    <?php if ($message['sender'] == $activeUser): ?>
                        <div class="message-settings">
                            <span class="settings-icon" onclick="toggleSettings(<?php echo $message['id']; ?>)">&#8942;</span>
                            <div class="settings-dropdown" id="settings-<?php echo $message['id']; ?>">
                                <button class="delete-btn" onclick="showDeleteConfirmation(<?php echo $message['id']; ?>)">Delete</button>
                                <button class="cancel-btn" onclick="toggleSettings(<?php echo $message['id']; ?>)">Cancel</button>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

                <?php $lastTimestamp = $message['timestamp']; // Update last timestamp ?>

            <?php endforeach; ?>
        <?php else: ?>
            <p>No messages yet.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div id="deleteConfirmationModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeDeleteModal">&times;</span>
        <h2>Delete Message?</h2>
        <p>Are you sure you want to delete this message?</p>
        <form method="post" id="deleteForm">
            <input type="hidden" name="message_id" id="deleteMessageId" value="">
            <button type="submit" name="delete_message" class="btn btn-danger">Delete</button>
            <button type="button" class="btn btn-secondary" id="cancelDeleteBtn">Cancel</button>
        </form>
    </div>
</div>

<div class="message-input-container">
    <form method="post" class="message-input-area">
        <input type="hidden" name="receiver" value="<?php echo htmlspecialchars($receiverToMessage); ?>">
        <input type="text" name="message" class="message-input" placeholder="Type your message..." required>
        <button type="submit" name="send_message" class="btn btn-primary"> <i class="fas fa-paper-plane"></i></button>
    </form>
</div>




        <div id="newMessageModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>New Message</h2>
                <form method="post" action="send_message.php">
                    <label for="receiver">To:</label>
                    <input type="text" name="receiver" id="receiver" required>
                    <label for="message">Message:</label>
                    <textarea name="message" required></textarea>
                    <button type="submit" name="send_message" class="btn btn-primary">Send</button>
                    <button type="button" class="btn btn-secondary" id="cancelMessageBtn">Cancel</button>
                </form>
            </div>
        </div>

        <script>
            var modal = document.getElementById("newMessageModal");
            var btn = document.getElementById("newMessageBtn");
            var span = document.getElementsByClassName("close")[0];
            var cancelBtn = document.getElementById("cancelMessageBtn");

            btn.onclick = function() {
                modal.style.display = "block";
            }

            span.onclick = function() {
                modal.style.display = "none";
            }

            cancelBtn.onclick = function() {
                modal.style.display = "none";
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }

            document.querySelector("form").addEventListener("submit", function(event) {
    let receiver = document.getElementById("receiver").value;
    let message = document.querySelector("textarea").value;

    if (receiver.trim() === "" || message.trim() === "") {
        alert("Please fill in all fields.");
        event.preventDefault(); // I-prevent lang kung may kulang
    }
    });
        </script>




<script>
    var deleteModal = document.getElementById("deleteConfirmationModal");
    var closeDeleteModal = document.getElementById("closeDeleteModal");
    var cancelDeleteBtn = document.getElementById("cancelDeleteBtn");
    var deleteMessageIdInput = document.getElementById("deleteMessageId");

    function toggleSettings(messageId) {
        var settingsDropdown = document.getElementById('settings-' + messageId);
        if (settingsDropdown.style.display === "block") {
            settingsDropdown.style.display = "none";
        } else {
            settingsDropdown.style.display = "block";
        }
    }

    function showDeleteConfirmation(messageId) {
        deleteMessageIdInput.value = messageId;
        deleteModal.style.display = "block";
        var settingsDropdown = document.getElementById('settings-' + messageId);
        settingsDropdown.style.display = "none";
    }

    closeDeleteModal.onclick = function() {
        deleteModal.style.display = "none";
    }

    cancelDeleteBtn.onclick = function() {
        deleteModal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == deleteModal) {
            deleteModal.style.display = "none";
        }
    }

//scroll
    function scrollToBottom() {
    var messageContainer = document.querySelector(".messages");
    messageContainer.scrollTop = messageContainer.scrollHeight;
}

// Auto-scroll sa bottom pagkatapos mag-load ng page
window.onload = function() {
    scrollToBottom();
};

// Auto-scroll din kapag nag-submit ng message
document.querySelector("form.message-input-area").addEventListener("submit", function() {
    setTimeout(scrollToBottom, 100); // Delay ng konti para sure na naprocess na ang bagong message
});

//responsive
document.addEventListener("DOMContentLoaded", function() {
    let toggleBtn = document.createElement("button");
    toggleBtn.textContent = "☰ Conversations";
    toggleBtn.classList.add("btn", "btn-primary", "toggle-conversations");
    document.querySelector(".conversations").prepend(toggleBtn);

    toggleBtn.addEventListener("click", function() {
        let conv = document.querySelector(".conversations");
        conv.style.display = conv.style.display === "none" ? "block" : "none";
    });

    // Auto-hide conversation list on small screens after selecting a chat
    document.querySelectorAll(".conversation-item").forEach(item => {
        item.addEventListener("click", function() {if (window.innerWidth <= 768) {
                    document.querySelector(".conversations").style.display = "none";
                }
        });
    });
});

const conversationItems = document.querySelectorAll('.conversation-item');

conversationItems.forEach(item => {
    item.addEventListener('click', function() {conversationItems.forEach(i => i.classList.remove('active')); // Remove active class from all
        this.classList.add('active'); // Add active class to the clicked item
    });
});

</script>

</body>
</html>

<?php
$conn->close();
?>