<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access.");
}

include "includes/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = $_POST['item_name'];
    $quantity = $_POST['quantity'];

    // Check if the item already exists
    $query = "SELECT * FROM inventory WHERE item_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $item_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Item exists, update the quantity
        $existing_item = $result->fetch_assoc();
        $new_quantity = $existing_item['quantity'] + $quantity;

        $update_query = "UPDATE inventory SET quantity = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ii", $new_quantity, $existing_item['id']);

        if ($update_stmt->execute()) {
            echo "Item quantity updated successfully!";
        } else {
            echo "Error updating item quantity.";
        }
    } else {
        // Item does not exist, insert a new item
        $insert_query = "INSERT INTO inventory (item_name, quantity) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("si", $item_name, $quantity);

        if ($insert_stmt->execute()) {
            echo "Item added successfully!";
        } else {
            echo "Error adding item.";
        }
    }
}
?>
