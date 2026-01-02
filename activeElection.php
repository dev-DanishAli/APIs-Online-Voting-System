<?php
include 'connectDb.php';
global $conn;

// Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// JSON helper
function sendJson($statusCode, $data) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Only GET allowed
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJson(405, [
        'message' => 'Method Not Allowed'
    ]);
}

// Query active election
$query = "SELECT election_id, status FROM elections WHERE status = 'active' LIMIT 1";
$result = mysqli_query($conn, $query);

if (!$result) {
    sendJson(500, [
        'message' => 'Database query failed'
    ]);
}

if (mysqli_num_rows($result) === 0) {
    sendJson(404, [
        'message' => 'No active election found'
    ]);
}

$row = mysqli_fetch_assoc($result);

sendJson(200, [
    'message' => 'Active election retrieved successfully',
    'election_id' => $row['election_id'],
    'status' => $row['status']
]);

?>