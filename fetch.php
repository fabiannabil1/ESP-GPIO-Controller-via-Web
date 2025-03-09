<?php
include_once 'esp-database.php';

header('Content-Type: application/json');

$result = getAllOutputs();
$outputs = array();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $outputs[] = array(
            'id' => (int)$row['id'],
            'state' => (int)$row['state']
        );
    }
}

echo json_encode($outputs);
?>