<?php
include_once('esp-database.php');

$result = getAllOutputs();
$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id' => $row["id"],
            'state' => $row["state"]
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($data);
?>
