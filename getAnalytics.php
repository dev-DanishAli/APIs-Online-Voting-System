<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include "connectDb.php"; // DB connection

try {

    /* ----------------------------------------
       STEP 1: Get Active Election ID & Title
    ---------------------------------------- */
    $electionQuery = "
        SELECT election_id, title 
        FROM elections 
        WHERE status = 'active'
        LIMIT 1
    ";

    $electionResult = mysqli_query($conn, $electionQuery);

    if (!$electionResult || mysqli_num_rows($electionResult) === 0) {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "message" => "No active election found"
        ]);
        exit;
    }

    $electionRow   = mysqli_fetch_assoc($electionResult);
    $election_id   = (int)$electionRow['election_id'];
    $electionTitle = $electionRow['title'];

    /* ----------------------------------------
       STEP 2: Vote Columns
    ---------------------------------------- */
    $voteColumns = [
        "pres_id",
        "vp_id",
        "gs_id",
        "cs_rep",
        "bba_rep",
        "asaf_rep",
        "se_rep"
    ];

    $votes = [];

    /* ----------------------------------------
       STEP 3: Fetch Votes for Active Election
    ---------------------------------------- */
    foreach ($voteColumns as $col) {

        $query = "
            SELECT 
                v.$col AS candidate_id,
                c.name AS candidate_name,
                p.position_name,
                COUNT(*) AS vote_count
            FROM votes v
            LEFT JOIN candidates c 
                ON c.candidate_id = v.$col 
                AND c.election_id = v.election_id
            LEFT JOIN positions p 
                ON p.position_id = c.position_id
            WHERE v.election_id = $election_id
            GROUP BY v.$col
        ";

        $result = mysqli_query($conn, $query);

        $votes[$col] = [];

        while ($row = mysqli_fetch_assoc($result)) {
            if (!empty($row['candidate_id'])) {
                $votes[$col][] = [
                    "candidate_id"   => $row['candidate_id'],
                    "candidate_name" => $row['candidate_name'],
                    "position_name"  => $row['position_name'],
                    "vote_count"     => (int)$row['vote_count']
                ];
            }
        }
    }

    /* ----------------------------------------
       STEP 4: Final Response
    ---------------------------------------- */
    echo json_encode([
        "status" => "success",
        "active_election_id" => $election_id,
        "active_election_title" => $electionTitle,
        "data" => $votes
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>
