
<?php

// Dummy University Students Record for Vote Eligibility

include 'connectDb.php';
global $conn;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    $query = 'SELECT * FROM studentsinfodb';
    $res = mysqli_query($conn, $query);

    if ($res) {
        if (mysqli_num_rows($res) > 0) {
            $data = [];
            while ($row = mysqli_fetch_assoc($res)) {
                $data[] = $row;
            }
            echo json_encode([
                'status' => 200,
                'message' => 'Students Info retrived Successfully',
                'studentsdata' => $data
            ]);
        } else {
            echo json_encode([
                'status' => 404,
                'message' => "No Students Found"
            ]);
        }
    } else {
        echo json_encode([
            'status' => 500,
            'message' => 'Internal Error'
        ]);
    }
} else {
    echo json_encode([
        'status' => 400,
        'message' => 'Request Method Not Allowed'
    ]);
}
