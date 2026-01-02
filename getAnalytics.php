<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include "connectDb.php"; // your DB connection

$election_id = $_GET['election_id'] ?? null;

if (!$election_id) {
    http_response_code(400);
    echo json_encode(["error" => "Election ID is required"]);
    exit;
}

try {
    // Vote columns in your votes table
    $voteColumns = ["pres_id", "vp_id", "gs_id", "cs_rep", "bba_rep", "asaf_rep", "se_rep"];
    $votes = [];

    foreach ($voteColumns as $col) {
        // Count votes and get candidate info
        $stmt = $conn->prepare("
            SELECT 
                v.$col AS candidate_id,
                c.name AS candidate_name,
                p.position_name,
                COUNT(*) AS vote_count
            FROM votes v
            LEFT JOIN candidates c 
                ON c.candidate_id = v.$col AND c.election_id = v.election_id
            LEFT JOIN positions p 
                ON p.position_id = c.position_id
            WHERE v.election_id = ?
            GROUP BY v.$col
        ");
        $stmt->bind_param("i", $election_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $votes[$col] = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['candidate_id']) {
                $votes[$col][] = [
                    "candidate_id" => $row['candidate_id'],
                    "candidate_name" => $row['candidate_name'],
                    "position_name" => $row['position_name'],
                    "vote_count" => (int)$row['vote_count']
                ];
            }
        }
    }

    echo json_encode([
        "status" => "success",
        "election_id" => (int)$election_id,
        "data" => $votes
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
