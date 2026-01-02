<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

include "connectDb.php";

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

$voter_id    = $data['voter_id'] ?? '';
$pres_id     = $data['pres_id'] ?? '';
$vp_id       = $data['vp_id'] ?? '';
$gs_id       = $data['gs_id'] ?? '';
$cs_rep      = $data['cs_rep'] ?? '';
$bba_rep     = $data['bba_rep'] ?? '';
$asaf_rep    = $data['asaf_rep'] ?? '';
$se_rep      = $data['se_rep'] ?? '';
$election_id = $data['election_id'] ?? '';

// Check for missing fields
if (!$voter_id || !$election_id) {
    echo json_encode([
        "status" => 400,
        "message" => "Voter ID or Election ID missing"
    ]);
    exit;
}

// Check if voter already voted
$check_query = "SELECT voter_id FROM votes WHERE voter_id='$voter_id' AND election_id='$election_id'";
$result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($result) > 0) {
    echo json_encode([
        "status" => 409,
        "message" => "You have already voted in this election"
    ]);
    exit;
}

// Insert vote
$insert_query = "
    INSERT INTO votes 
    (voter_id, pres_id, vp_id, gs_id, cs_rep, bba_rep, asaf_rep, se_rep, election_id) 
    VALUES 
    ('$voter_id', '$pres_id', '$vp_id', '$gs_id', '$cs_rep', '$bba_rep', '$asaf_rep', '$se_rep', '$election_id')
";

if (mysqli_query($conn, $insert_query)) {
    echo json_encode([
        "status" => 200,
        "message" => "Vote cast successfully"
    ]);
} else {
    echo json_encode([
        "status" => 500,
        "message" => "Database error",
        "error" => mysqli_error($conn)
    ]);
}

mysqli_close($conn);
?>
