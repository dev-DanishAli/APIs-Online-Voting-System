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

// get total candidates
$query = "SELECT COUNT(*) AS total_candidates FROM candidates inner join elections 
on candidates.election_id = elections.election_id where status = 'active'";

$result = mysqli_query($conn, $query);
if (!$result) {
    sendJson(500, [
        'message' => 'Database query failed'
    ]);
}
$row = mysqli_fetch_assoc($result);
$totalCandidates = $row['total_candidates'];
// get total voters
$query = "SELECT COUNT(*) AS reg_voters FROM studentsinfodb";
$result = mysqli_query($conn, $query);
if (!$result) {
    sendJson(500, [
        'message' => 'Database query failed'
    ]);
}
$row = mysqli_fetch_assoc($result);
$regVoters = $row['reg_voters'];

// ongoing election
$query = "SELECT title FROM elections WHERE status = 'active'";
$result = mysqli_query($conn, $query);
if (!$result) {
    sendJson(500, [
        'message' => 'Database query failed'
    ]);
}
$row = mysqli_fetch_assoc($result);
$ongoingElections = $row['title'];

// completed elections
$query = "SELECT COUNT(*) AS ended_elections FROM elections WHERE status = 'ended'";
$result = mysqli_query($conn, $query);
if (!$result) {
    sendJson(500, [
        'message' => 'Database query failed'
    ]);
}
$row = mysqli_fetch_assoc($result);
$ended_elections = $row['ended_elections'];

// castd voters count
$query = "SELECT COUNT(*) AS casted_voters FROM votes";
$result = mysqli_query($conn, $query);
if (!$result) {
    sendJson(500, [
        'message' => 'Database query failed'
    ]);
}
$row = mysqli_fetch_assoc($result);
$casted_voters = $row['casted_voters'];

sendJson(200, [
    'message' => 'Dashboard statistics retrieved successfully',
    'total_candidates' => $totalCandidates,
    'reg_voters' => $regVoters,
    'ongoing_elections' => $ongoingElections,
    'ended_elections' => $ended_elections,
    'casted_voters' => $casted_voters
]);


?>