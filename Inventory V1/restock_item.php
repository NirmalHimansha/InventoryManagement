<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access.");
}

include "includes/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = $_POST['item_id'];
    $restock_quantity = $_POST['restock_quantity'];

    $query = "UPDATE inventory SET quantity = quantity + ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $restock_quantity, $item_id);

    if ($stmt->execute()) {
        echo "Item restocked successfully!";
    } else {
        echo "Error restocking item.";
    }
}
?>
