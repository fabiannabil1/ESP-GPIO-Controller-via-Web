<?php
include_once('esp-database.php');

$action = $id = $name = $gpio = $state = $board = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["action"])) {
        $action = test_input($_POST["action"]);
    }
    if ($action == "output_create") {
        $name  = isset($_POST["name"])  ? test_input($_POST["name"])  : "";
        $board = isset($_POST["board"]) ? test_input($_POST["board"]) : "";
        $gpio  = isset($_POST["gpio"])  ? test_input($_POST["gpio"])  : "";
        $state = isset($_POST["state"]) ? test_input($_POST["state"]) : "";
        $type  = isset($_POST["type"])  ? test_input($_POST["type"])  : ""; // Menambahkan field type

        $result = createOutput($name, $board, $gpio, $state, $type);

        $result2 = getBoard($board);
        if (!$result2->fetch_assoc()) {
            createBoard($board);
        }
        echo $result;
    } else {
        echo "No data posted with HTTP POST.";
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["action"])) {
        $action = test_input($_GET["action"]);
    }
    if ($action == "outputs_state") {
        $board = isset($_GET["board"]) ? test_input($_GET["board"]) : "";
        $result = getAllOutputStates($board);
        $rows = array(); // Inisialisasi array untuk output JSON

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Menghasilkan JSON dengan format: "gpio": { "type": "...", "state": "..." }
                $rows[$row["gpio"]] = array(
                    "type"  => $row["type"],
                    "state" => $row["state"]
                );
            }
        }
        
        // Update waktu board jika board ditemukan
        $boardResult = getBoard($board);
        if ($boardResult && $boardResult->fetch_assoc()) {
            updateLastBoardTime($board);
        }
        echo json_encode($rows);
    } elseif ($action == "output_update") {
        $id    = isset($_GET["id"])    ? test_input($_GET["id"])    : "";
        $state = isset($_GET["state"]) ? test_input($_GET["state"]) : "";
        $result = updateOutput($id, $state);
        echo $result;
    } elseif ($action == "output_delete") {
        $id = isset($_GET["id"]) ? test_input($_GET["id"]) : "";
        $board_id = "";
        $boardResult = getOutputBoardById($id);
        if ($boardResult && $row = $boardResult->fetch_assoc()) {
            $board_id = $row["board"];
        }
        $result = deleteOutput($id);
        $result2 = getAllOutputStates($board_id);
        if (!$result2 || !$result2->fetch_assoc()) {
            deleteBoard($board_id);
        }
        echo $result;
    } else {
        echo "Invalid HTTP request.";
    }
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
