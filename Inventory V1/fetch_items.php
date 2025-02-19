<?php
include "includes/db.php";

$query = "SELECT * FROM inventory";
$result = $conn->query($query);

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode($items);
?>
