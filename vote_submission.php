<?php

// This API ensure Vote Increment for a Particular Candidate and allow only PUT Method

include 'connectDb.php';
global $conn;


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {

    $candidateID = null;
    if (isset($_GET['id'])) {

        $candidateID = (int)mysqli_real_escape_string($conn, $_GET['id']);
    } else {
        echo json_encode([
            'status' => 400,
            'message' => 'Candidate ID is required'
        ]);
        exit;
    }

    $query = "UPDATE candidates set vote_count = vote_count + 1 WHERE candidate_id = $candidateID";

    $res = mysqli_query($conn, $query);

    if ($res) {
        if (mysqli_affected_rows($conn) > 0) {
            echo json_encode([
                'status' => 200,
                'message' => 'Vote Count Submitted'
            ]);
        } else {
            echo json_encode([
                'status' => 404,
                'message' => "Candidate ID $candidateID not found or no vote submitted."
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

?>