<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Not authenticated"]);
    exit;
}

include 'config.php';

$user_id = $_SESSION['user_id'];
$response = ["messages" => []];

try {
    $stmt = $conn->prepare("SELECT user_message, bot_response FROM chat_messages WHERE user_id = ? ORDER BY created_at ASC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $response["messages"][] = $row;
    }
    
    $stmt->close();
} catch (Exception $e) {
    $response["error"] = "Database error: " . $e->getMessage();
}

echo json_encode($response);
exit;
?>