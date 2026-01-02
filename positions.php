<?php

include 'connectDb.php';
global $conn;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET,OPTIONS");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    $query = 'SELECT * FROM positions';
    $res = mysqli_query($conn, $query);

    if ($res) {
        if (mysqli_num_rows($res) > 0) {
            $data = [];
            while ($row = mysqli_fetch_assoc($res)) {
                $data[] = $row;
            }
            echo json_encode([
                'status' => 200,
                'positions' => $data
            ]);
        } else {
            echo json_encode([
                'status' => 404,
                'message' => "No positions Found"
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
