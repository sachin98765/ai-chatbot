<?php
function get_db_connection() {
    $db_host = "127.0.0.1";
    $db_user = "root";
    $db_pass = "";
    $db_name = "ai_chatbot";

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        http_response_code(500);
        echo json_encode(["error" => "Database connection failed."]);
        exit;
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}
