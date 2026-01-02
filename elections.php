<?php
mysqli_report(MYSQLI_REPORT_OFF);
include 'connectDb.php';
global $conn;

// CORS & JSON headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

// GET elections
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $query = 'SELECT * FROM elections';
    $res = mysqli_query($conn, $query);

    if ($res) {
        if (mysqli_num_rows($res) > 0) {
            $data = [];
            while ($row = mysqli_fetch_assoc($res)) {
                $data[] = $row;
            }
            echo json_encode([
                'status' => 200,
                'message' => 'Elections retrieved successfully',
                'elections' => $data
            ]);
        } else {
            echo json_encode([
                'status' => 404,
                'message' => "No Elections Found"
            ]);
        }
    } else {
        echo json_encode([
            'status' => 500,
            'message' => 'Internal Server Error',
            'error' => mysqli_error($conn)
        ]);
    }
}

// DELETE election
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    $id = intval($_GET['id'] ?? 0);

    $query = "DELETE FROM elections WHERE election_id = $id";
    $res = mysqli_query($conn, $query);

    if (!$res) {
        http_response_code(500);
        echo json_encode([
            'status' => 500,
            'message' => 'Error deleting election: ' . mysqli_error($conn)
        ]);
        exit;
    }

    if (mysqli_affected_rows($conn) === 0) {
        http_response_code(404);
        echo json_encode([
            'status' => 404,
            'message' => 'Election not found'
        ]);
        exit;
    }

    http_response_code(200);
    echo json_encode([
        'status' => 200,
        'message' => 'Election deleted successfully'
    ]);
    exit;
}





// POST new election
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        echo json_encode([
            'status' => 400,
            'message' => 'Invalid JSON input'
        ]);
        exit;
    }

    $title = mysqli_real_escape_string($conn, $data['title']);
    $status = mysqli_real_escape_string($conn, $data['status']);
    $created_at = mysqli_real_escape_string($conn, $data['created_at']);

   $query = "INSERT INTO elections (title, status, created_at)
              VALUES ('$title', '$status', '$created_at')";

    $res = mysqli_query($conn, $query);

    if ($res) {
        echo json_encode([
            "status" => 200,
            "message" => "Election added successfully"
        ]);
    } else {
        echo json_encode([
            "status" => 400,
            "message" => "Error adding election: " . mysqli_error($conn)
        ]);
    }
}

// PUT update election
if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    if (!isset($_GET['id'])) {
        echo json_encode([
            'status' => 400,
            'message' => 'ID parameter is required for update'
        ]);
        exit;
    }

    $updateID = (int)mysqli_real_escape_string($conn, $_GET['id']);
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        echo json_encode([
            'status' => 400,
            'message' => 'Invalid JSON input'
        ]);
        exit;
    }

    $election_id = (int)mysqli_real_escape_string($conn, $data['election_id']);
    $title = mysqli_real_escape_string($conn, $data['title']);
    $status = mysqli_real_escape_string($conn, $data['status']);
    $created_at = mysqli_real_escape_string($conn, $data['created_at']);

    $query = "UPDATE elections SET 
              election_id = $election_id, 
              title = '$title',
              status = '$status',
              created_at = '$created_at' 
              WHERE election_id = $updateID";

    $res = mysqli_query($conn, $query);

    if ($res) {
        echo json_encode([
            "status" => 200,
            "message" => "Election updated successfully"
        ]);
    } else {
        echo json_encode([
            "status" => 400,
            "message" => "Error updating election: " . mysqli_error($conn)
        ]);
    }
}
