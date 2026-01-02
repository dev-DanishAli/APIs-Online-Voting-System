<?php

include 'connectDb.php';
global $conn;

// CORS & headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

// Handle OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Helper JSON response
function sendJson($data) {
    echo json_encode($data);
    exit;
}

//
// ==========================
// GET — Retrieve all candidates
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $electionId = isset($_GET['electionId']) ? mysqli_real_escape_string($conn, $_GET['electionId']) : 0;

    $query = "SELECT 
        c.candidate_id,
        c.name,
        c.email,
        c.cnic_number,
        d.dept_name AS department,
        c.semester,
        p.position_name AS position,
        c.election_sign
    FROM candidates c
    JOIN departments d ON c.dept_id = d.dept_id
    JOIN positions p ON c.position_id = p.position_id
    WHERE c.election_id = '$electionId'";

    $res = mysqli_query($conn, $query);

    if ($res) {
        if (mysqli_num_rows($res) > 0) {
            $data = [];
            while ($row = mysqli_fetch_assoc($res)) {
                // Convert image blob → base64
                if (!empty($row['election_sign'])) {
                    $row['election_sign'] = base64_encode($row['election_sign']);
                }
                $data[] = $row;
            }
            sendJson([
                'status' => 200,
                'message' => 'Candidates retrieved successfully',
                'candidates' => $data
            ]);
        } else {
            sendJson([
                'status' => 404,
                'message' => 'No candidates found'
            ]);
        }
    } else {
        sendJson([
            'status' => 500,
            'message' => 'Database error',
            'error' => mysqli_error($conn)
        ]);
    }
}

//
// ==========================
// DELETE — Delete candidate
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    if (isset($_GET['id'])) {
        $id = mysqli_real_escape_string($conn, $_GET['id']);
        $query = "DELETE FROM candidates WHERE candidate_id = '$id'";
        $res = mysqli_query($conn, $query);

        if ($res) {
            sendJson([
                'status' => 200,
                'message' => "Candidate with ID '$id' deleted successfully"
            ]);
        } else {
            sendJson([
                'status' => 400,
                'message' => 'Error deleting candidate',
                'error' => mysqli_error($conn)
            ]);
        }

    } else {
        sendJson([
            'status' => 400,
            'message' => 'ID parameter missing'
        ]);
    }
}

//
// ==========================
// POST — Add new candidate
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $candidate = [];

    // -------------------------
    // HANDLE INPUT
    // -------------------------
    if (isset($_FILES['election_sign'])) {
        $candidate['candidate_id'] = mysqli_real_escape_string($conn, $_POST['candidate_id']);
        $candidate['name'] = mysqli_real_escape_string($conn, $_POST['name']);
        $candidate['email'] = mysqli_real_escape_string($conn, $_POST['email']);
        $candidate['cnic_number'] = mysqli_real_escape_string($conn, $_POST['cnic_number']);
        $candidate['dept_name'] = mysqli_real_escape_string($conn, $_POST['dept_name']);
        $candidate['semester'] = mysqli_real_escape_string($conn, $_POST['semester']);
        $candidate['position_name'] = mysqli_real_escape_string($conn, $_POST['position_name']);
        $candidate['election_id'] = mysqli_real_escape_string($conn, $_POST['election_id']);
        $candidate['election_sign'] = mysqli_real_escape_string(
            $conn,
            file_get_contents($_FILES['election_sign']['tmp_name'])
        );
    } else {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            sendJson(['status' => 400, 'message' => 'Invalid JSON input']);
        }

        $candidate['candidate_id'] = mysqli_real_escape_string($conn, $data['candidate_id']);
        $candidate['name'] = mysqli_real_escape_string($conn, $data['name']);
        $candidate['email'] = mysqli_real_escape_string($conn, $data['email']);
        $candidate['cnic_number'] = mysqli_real_escape_string($conn, $data['cnic_number']);
        $candidate['dept_name'] = mysqli_real_escape_string($conn, $data['dept_name']);
        $candidate['semester'] = mysqli_real_escape_string($conn, $data['semester']);
        $candidate['position_name'] = mysqli_real_escape_string($conn, $data['position_name']);
        $candidate['election_id'] = mysqli_real_escape_string($conn, $data['election_id']);
        $candidate['election_sign'] = isset($data['election_sign']) ? mysqli_real_escape_string(
            $conn,
            base64_decode($data['election_sign'])
        ) : '';
    }

    // -------------------------
    // FETCH DEPARTMENT ID
    // -------------------------
    $deptQuery = "SELECT dept_id FROM departments WHERE dept_name = '{$candidate['dept_name']}' LIMIT 1";
    $deptResult = mysqli_query($conn, $deptQuery);
    if (mysqli_num_rows($deptResult) === 0) {
        sendJson(['status' => 400, 'message' => 'Invalid department']);
    }
    $deptRow = mysqli_fetch_assoc($deptResult);
    $dept_id = $deptRow['dept_id'];

    // -------------------------
    // FETCH POSITION ID
    // -------------------------
    $posQuery = "SELECT position_id FROM positions WHERE position_name = '{$candidate['position_name']}' LIMIT 1";
    $posResult = mysqli_query($conn, $posQuery);
    if (mysqli_num_rows($posResult) === 0) {
        sendJson(['status' => 400, 'message' => 'Invalid position']);
    }
    $posRow = mysqli_fetch_assoc($posResult);
    $position_id = $posRow['position_id'];

    // -------------------------
    // INSERT CANDIDATE
    // -------------------------
    $query = "
        INSERT INTO candidates
        (candidate_id, name, email, cnic_number, dept_id, semester, position_id, election_sign, election_id)
        VALUES (
            '{$candidate['candidate_id']}',
            '{$candidate['name']}',
            '{$candidate['email']}',
            '{$candidate['cnic_number']}',
            '{$dept_id}',
            '{$candidate['semester']}',
            '{$position_id}',
            '{$candidate['election_sign']}',
            '{$candidate['election_id']}'
        )
    ";

    if (mysqli_query($conn, $query)) {
        sendJson(['status' => 200, 'message' => 'Candidate added successfully']);
    } else {
        sendJson(['status' => 400, 'message' => 'Error adding candidate', 'error' => mysqli_error($conn)]);
    }
}

//
// ==========================
// PUT — Update candidate
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {

    if (!isset($_GET['id'])) {
        sendJson(['status' => 400, 'message' => 'ID parameter is required for update']);
    }

    $updateID = mysqli_real_escape_string($conn, $_GET['id']);
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        sendJson(['status' => 400, 'message' => 'Invalid JSON input']);
    }

    $candidate = [
        'candidate_id' => mysqli_real_escape_string($conn, $data['candidate_id']),
        'name' => mysqli_real_escape_string($conn, $data['name']),
        'email' => mysqli_real_escape_string($conn, $data['email']),
        'cnic_number' => mysqli_real_escape_string($conn, $data['cnic_number']),
        'dept_name' => mysqli_real_escape_string($conn, $data['dept_name']),
        'semester' => mysqli_real_escape_string($conn, $data['semester']),
        'position_name' => mysqli_real_escape_string($conn, $data['position_name']),
        'election_id' => mysqli_real_escape_string($conn, $data['election_id']),
        'election_sign' => isset($data['election_sign']) ? mysqli_real_escape_string($conn, base64_decode($data['election_sign'])) : ''
    ];

    // Fetch dept_id
    $deptQuery = "SELECT dept_id FROM departments WHERE dept_name = '{$candidate['dept_name']}' LIMIT 1";
    $deptResult = mysqli_query($conn, $deptQuery);
    if (mysqli_num_rows($deptResult) === 0) sendJson(['status'=>400,'message'=>'Invalid department']);
    $dept_id = mysqli_fetch_assoc($deptResult)['dept_id'];

    // Fetch position_id
    $posQuery = "SELECT position_id FROM positions WHERE position_name = '{$candidate['position_name']}' LIMIT 1";
    $posResult = mysqli_query($conn, $posQuery);
    if (mysqli_num_rows($posResult) === 0) sendJson(['status'=>400,'message'=>'Invalid position']);
    $position_id = mysqli_fetch_assoc($posResult)['position_id'];

    // Update candidate
    $query = "UPDATE candidates SET 
        candidate_id = '{$candidate['candidate_id']}',
        name = '{$candidate['name']}',
        email = '{$candidate['email']}',
        cnic_number = '{$candidate['cnic_number']}',
        dept_id = '$dept_id',
        semester = '{$candidate['semester']}',
        position_id = '$position_id',
        election_id = '{$candidate['election_id']}',
        election_sign = '{$candidate['election_sign']}'
    WHERE candidate_id = '$updateID'";

    if (mysqli_query($conn, $query)) {
        sendJson(['status' => 200, 'message' => "Candidate with ID '$updateID' updated successfully"]);
    } else {
        sendJson(['status' => 400, 'message' => 'Error updating candidate', 'error' => mysqli_error($conn)]);
    }
}

?>
