<?php
include_once('esp-database.php');

header('Content-Type: application/json'); // Tambahkan header JSON

// Inisialisasi variabel
$action = $gpio = $state = $board = "";

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $action = isset($_GET["action"]) ? test_input($_GET["action"]) : '';
    
    if ($action === "input_update") {
        $gpio  = isset($_GET["gpio"]) ? test_input($_GET["gpio"]) : '';
        $state = isset($_GET["state"]) ? test_input($_GET["state"]) : '';
        $board = isset($_GET["board"]) ? test_input($_GET["board"]) : '';
        
        if ($gpio !== '' && $state !== '' && $board !== '') {
            // Gunakan fungsi updateInput yang sudah ada
            $result = updateInput($board, $gpio, $state);
            
            echo json_encode([
                "success" => $result,
                "message" => $result ? "Updated successfully" : "Update failed"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Missing parameters"
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Invalid action"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>