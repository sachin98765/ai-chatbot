<?php
session_start();
header("Content-Type: application/json");
require_once "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (!isset($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$api_key = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXx";
$chat_model = "nvidia/nemotron-3-nano-omni-30b-a3b-reasoning:free";

$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid request payload"]);
    exit;
}

if (!isset($input['message']) || !is_string($input['message'])) {
    http_response_code(400);
    echo json_encode(["error" => "No message provided"]);
    exit;
}

$message = trim($input['message']);
if ($message === '') {
    http_response_code(400);
    echo json_encode(["error" => "Message cannot be empty"]);
    exit;
}

$userId = intval($_SESSION['user']['id']);
$conversationId = isset($input['conversation_id']) && is_numeric($input['conversation_id']) ? intval($input['conversation_id']) : null;
$newConversation = !empty($input['new_conversation']);

try {
    $conn = get_db_connection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Unable to connect to the database."]);
    exit;
}

if ($conversationId) {
    $stmt = $conn->prepare("SELECT id FROM ai_conversations WHERE id = ? AND user_id = ? LIMIT 1");
    $stmt->bind_param("ii", $conversationId, $userId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $conversationId = null;
    }
    $stmt->close();
}

if ($newConversation || !$conversationId) {
    $stmt = $conn->prepare("SELECT id FROM ai_conversations WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($lastConversationId);
    if ($stmt->fetch() && !$newConversation) {
        $conversationId = intval($lastConversationId);
    }
    $stmt->close();
}

if (!$conversationId) {
    $title = mb_substr($message, 0, 70);
    if ($title === '') {
        $title = 'New chat';
    }

    $stmt = $conn->prepare("INSERT INTO ai_conversations (user_id, title) VALUES (?, ?)");
    $stmt->bind_param("is", $userId, $title);
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(["error" => "Unable to create conversation."]);
        exit;
    }
    $conversationId = intval($stmt->insert_id);
    $stmt->close();
}

$stmt = $conn->prepare("INSERT INTO ai_messages (conversation_id, role, content) VALUES (?, 'user', ?)");
$stmt->bind_param("is", $conversationId, $message);
$stmt->execute();
$stmt->close();

$url = "https://openrouter.ai/api/v1/chat/completions";
$data = [
    "model" => $chat_model,
    "messages" => [
        [
            "role" => "user",
            "content" => $message
        ]
    ],
    "max_tokens" => 1000
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $api_key,
    "HTTP-Referer: http://localhost",
    "X-Title: AI Chatbot"
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    http_response_code(500);
    echo json_encode(["error" => "Connection error: " . $error]);
    exit;
}

$response_data = json_decode($response, true);

if ($http_code !== 200) {
    $message = "API Error";
    if (isset($response_data['error']['message'])) {
        $message = $response_data['error']['message'];
    } elseif (isset($response_data['error'])) {
        $message = is_array($response_data['error']) ? json_encode($response_data['error']) : $response_data['error'];
    }

    http_response_code($http_code);
    echo json_encode(["error" => $message]);
    exit;
}

$ai_response = $response_data['choices'][0]['message']['content'] ?? 'No response generated';

$stmt = $conn->prepare("INSERT INTO ai_messages (conversation_id, role, content) VALUES (?, 'assistant', ?)");
$stmt->bind_param("is", $conversationId, $ai_response);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare("UPDATE ai_conversations SET updated_at = NOW() WHERE id = ?");
$stmt->bind_param("i", $conversationId);
$stmt->execute();
$stmt->close();

echo json_encode([
    "success" => true,
    "response" => $ai_response,
    "conversation_id" => $conversationId
]);
exit;
