<?php
include 'connectDb.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/* ---------- GET STATUS ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Missing id parameter']);
        exit;
    }

    $electionId = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "SELECT status FROM elections WHERE election_id = '$electionId'";
    $res = mysqli_query($conn, $query);

    if (mysqli_num_rows($res) === 0) {
        http_response_code(404);
        echo json_encode(['message' => 'Election not found']);
        exit;
    }

    echo json_encode([
        'status' => 200,
        'electionStatus' => mysqli_fetch_assoc($res)
    ]);
    exit;
}

/* ---------- UPDATE STATUS ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {

    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input['id'], $input['status'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Missing id or status'
        ]);
        exit;
    }

    $id = mysqli_real_escape_string($conn, $input['id']);
    $status = mysqli_real_escape_string($conn, $input['status']);

    // 🚫 NO STATUS CHANGES HERE
    $query = "UPDATE elections SET status='$status' WHERE election_id='$id'";

    if (mysqli_query($conn, $query)) {
        echo json_encode([
            'success' => true,
            'newStatus' => $status
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database update failed'
        ]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['message' => 'Method Not Allowed']);
