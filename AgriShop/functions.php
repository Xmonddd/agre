<?php

function getConversations($conn, $activeUser) {
    $sql = "SELECT u.fullname, u.profile_image, 
                   (SELECT COUNT(*) FROM messages 
                    WHERE messages.sender = u.fullname 
                      AND messages.receiver = ? 
                      AND messages.is_read = 0) AS unread_count 
            FROM users u 
            JOIN messages m ON u.fullname = m.sender OR u.fullname = m.receiver 
            WHERE m.sender = ? OR m.receiver = ? 
            GROUP BY u.fullname";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param("sss", $activeUser, $activeUser, $activeUser);
    $stmt->execute();
    $result = $stmt->get_result();

    $conversations = [];
    while ($row = $result->fetch_assoc()) {
        $conversations[] = [
            'fullname' => $row['fullname'],
            'profile_image' => !empty($row['profile_image']) ? $row['profile_image'] : 'uploads/default.png',
            'unread_count' => $row['unread_count'] // Added unread count
        ];
    }
    
    
    return $conversations;
}

?>



