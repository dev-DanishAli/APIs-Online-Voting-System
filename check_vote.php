<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET");
header("Content-Type: application/json");

include "connectDb.php";
global $conn;

// Try POST JSON first
$data = json_decode(file_get_contents("php://input"), true);
$voter_id = $data['voter_id'] ?? '';
$election_id = $data['election_id'] ?? '';

// Fallback to GET parameters if POST JSON is empty
if (!$voter_id) $voter_id = $_GET['voter_id'] ?? '';
if (!$election_id) $election_id = $_GET['election_id'] ?? '';

if (!$voter_id || !$election_id) {
    echo json_encode([
        "status" => 400,
        "message" => "Voter ID or Election ID missing"
    ]);
    exit;
}

$query = "SELECT voter_id FROM votes WHERE voter_id='$voter_id' AND election_id='$election_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    echo json_encode([
        "status" => 409,
        "message" => "You have already voted in this election"
    ]);
} else {
    echo json_encode([
        "status" => 200,
        "message" => "You can vote"
    ]);
}

mysqli_close($conn);
?>
