<?php
include "includes/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_id = $_POST['item_id'];
    $issued_quantity = $_POST['issued_quantity'];

    // Update the item quantity in the database
    $stmt = $conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE id = ?");
    $stmt->bind_param("ii", $issued_quantity, $item_id);

    if ($stmt->execute()) {
        echo "Item updated successfully!";
    } else {
        echo "Error updating item!";
    }
}
?>
