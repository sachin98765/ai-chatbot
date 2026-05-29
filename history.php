<?php
session_start();
header("Content-Type: application/json");
require_once "db.php";

if (!isset($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$userId = $_SESSION['user']['id'];

try {
    $conn = get_db_connection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Unable to connect to the database."]);
    exit;
}

if (isset($_GET['conversation_id']) && is_numeric($_GET['conversation_id'])) {
    $conversationId = intval($_GET['conversation_id']);

    $stmt = $conn->prepare(
        "SELECT id FROM ai_conversations WHERE id = ? AND user_id = ? LIMIT 1"
    );
    $stmt->bind_param("ii", $conversationId, $userId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        http_response_code(403);
        echo json_encode(["error" => "Conversation not found."]);
        exit;
    }
    $stmt->close();

    $stmt = $conn->prepare(
        "SELECT role, content, DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') AS created_at FROM ai_messages WHERE conversation_id = ? ORDER BY id ASC"
    );
    $stmt->bind_param("i", $conversationId);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    echo json_encode([
        "success" => true,
        "conversation_id" => $conversationId,
        "messages" => $messages
    ]);
    exit;
}

$limit = 15;
$stmt = $conn->prepare(
    "SELECT
        c.id,
        c.title,
        DATE_FORMAT(c.updated_at, '%Y-%m-%d %H:%i') AS updated_at,
        (SELECT content FROM ai_messages WHERE conversation_id = c.id AND role = 'user' ORDER BY id ASC LIMIT 1) AS first_user_message
    FROM ai_conversations c
    WHERE c.user_id = ?
    ORDER BY c.updated_at DESC
    LIMIT ?"
);
$stmt->bind_param("ii", $userId, $limit);
$stmt->execute();
$result = $stmt->get_result();

$conversations = [];
while ($row = $result->fetch_assoc()) {
    $conversations[] = $row;
}

echo json_encode([
    "success" => true,
    "conversations" => $conversations
]);
exit;
