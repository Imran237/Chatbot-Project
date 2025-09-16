<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Not authenticated"]);
    exit;
}

include 'config.php';

$user_id = $_SESSION['user_id'];
$response = [];

try {
    // Delete all chat messages for the current user
    $stmt = $conn->prepare("DELETE FROM chat_messages WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $response = ["success" => true, "message" => "Chat history cleared successfully"];
    } else {
        $response = ["success" => false, "error" => "Failed to clear chat history: " . $stmt->error];
    }
    
    $stmt->close();
} catch (Exception $e) {
    $response = ["success" => false, "error" => "Database error: " . $e->getMessage()];
}

// Always return JSON, even on failure
echo json_encode($response);
exit;
?>