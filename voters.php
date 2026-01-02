<?php
include 'connectDb.php';
global $conn;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET,POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM voters";
    $res = mysqli_query($conn, $query);

    if ($res) {
        $data = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $data[] = $row;
        }

        echo json_encode([
            'status' => 200,
            'message' => 'Voters fetched successfully',
            'voters' => $data
        ]);
    } else {
        echo json_encode([
            'status' => 500,
            'message' => 'Database error',
            'error' => mysqli_error($conn)
        ]);
    }
} 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newVoter = json_decode(file_get_contents('php://input'), true);

    $voterID = mysqli_real_escape_string($conn, $newVoter['voter_id']);
    $has_voted = mysqli_real_escape_string($conn, $newVoter['has_voted']);
    $voted_at = mysqli_real_escape_string($conn, $newVoter['voted_at']);

    $query = "INSERT INTO voters (voter_id, has_voted, voted_at) VALUES ('$voterID', '$has_voted', '$voted_at')";
    $res = mysqli_query($conn, $query);

    if ($res) {
        echo json_encode([
            'status' => 200,
            'message' => 'Vote submitted successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 400,
            'message' => 'Error submitting vote: ' . mysqli_error($conn)
        ]);
    }
}
