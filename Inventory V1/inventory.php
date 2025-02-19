<?php
include "includes/auth.php";
redirectIfNotLoggedIn();
include "includes/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isAdmin()) {
    $item_name = $_POST['item_name'];
    $quantity = $_POST['quantity'];
    
    $stmt = $conn->prepare("INSERT INTO inventory (item_name, quantity) VALUES (?, ?)");
    $stmt->bind_param("si", $item_name, $quantity);
    $stmt->execute();
}

$result = $conn->query("SELECT * FROM inventory");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>
<body>
    <h2>Inventory Management</h2>

    <?php if (isAdmin()): ?>
    <form method="POST">
        <input type="text" name="item_name" required placeholder="Item Name">
        <input type="number" name="quantity" required placeholder="Quantity">
        <button type="submit">Add Item</button>
    </form>
    <?php endif; ?>

    <table>
        <tr>
            <th>ID</th>
            <th>Item Name</th>
            <th>Quantity</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['item_name'] ?></td>
            <td><?= $row['quantity'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
<form id="issueForm">
    <select name="item_id" id="item_id" required>
        <option value="">Select Item</option>
        <!-- Items will be loaded dynamically -->
    </select>
    <input type="number" name="issued_quantity" id="issued_quantity" required placeholder="Quantity">
    <button type="submit">Issue</button>
</form>

<script>
document.getElementById('issueForm').addEventListener('submit', function(e) {
    e.preventDefault();

    let formData = new FormData(this);

    fetch('update_quantity.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => alert(data));
});
</script>

</html>
