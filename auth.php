<?php
session_start();
header("Content-Type: application/json");
require_once "db.php";

$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    $input = $_POST;
}

$action = $input['action'] ?? null;
if (!$action) {
    http_response_code(400);
    echo json_encode(["error" => "Missing action."]);
    exit;
}

function send_error($message, $code = 400) {
    http_response_code($code);
    echo json_encode(["error" => $message]);
    exit;
}

function get_post_value($input, $key) {
    return isset($input[$key]) ? trim($input[$key]) : '';
}

try {
    $conn = get_db_connection();
} catch (Exception $e) {
    send_error("Unable to connect to the database.", 500);
}

if ($action === 'signup') {
    $username = get_post_value($input, 'username');
    $email = get_post_value($input, 'email');
    $password = $input['password'] ?? '';
    $confirmPassword = $input['confirmPassword'] ?? '';

    if (strlen($username) < 2) {
        send_error("Username must be at least 2 characters long.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        send_error("Please enter a valid email address.");
    }

    if (strlen($password) < 8) {
        send_error("Password must be at least 8 characters.");
    }

    if ($password !== $confirmPassword) {
        send_error("Passwords do not match.");
    }

    $stmt = $conn->prepare("SELECT id FROM ai_users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        send_error("An account already exists with that email.");
    }
    $stmt->close();

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO ai_users (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $passwordHash);

    if (!$stmt->execute()) {
        send_error("Could not create account. Please try again later.", 500);
    }

    $userId = $stmt->insert_id;
    $stmt->close();

    $_SESSION['user'] = [
        'id' => $userId,
        'username' => $username,
        'email' => $email
    ];

    echo json_encode(["success" => true]);
    exit;
}

if ($action === 'login') {
    $email = get_post_value($input, 'email');
    $password = $input['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        send_error("Please enter a valid email address.");
    }

    if (strlen($password) < 8) {
        send_error("Password must be at least 8 characters.");
    }

    $stmt = $conn->prepare("SELECT id, username, password_hash FROM ai_users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($id, $username, $hash);

    if (!$stmt->fetch()) {
        send_error("Invalid email or password.", 401);
    }

    if (!password_verify($password, $hash)) {
        send_error("Invalid email or password.", 401);
    }

    $_SESSION['user'] = [
        'id' => $id,
        'username' => $username,
        'email' => $email
    ];

    $stmt->close();
    echo json_encode(["success" => true]);
    exit;
}

send_error("Invalid action.", 400);
