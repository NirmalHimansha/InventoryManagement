<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];

// Generate CSRF token for form security
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

include "includes/db.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Basic styling for better UI */
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h2 {
            color: #333;
        }
        form {
            margin-bottom: 20px;
        }
        input, select, button {
            margin: 5px;
            padding: 8px;
            font-size: 14px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .logout-btn {
            background-color: #f44336;
        }
        .logout-btn:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>

<h2>Inventory Chart</h2>
<canvas id="inventoryChart"></canvas>

<?php if ($role === 'admin') { ?>
    <h2>Add New Item</h2>
    <form id="addItemForm">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="text" name="item_name" required placeholder="Item Name" minlength="2" maxlength="50">
        <input type="number" name="quantity" required placeholder="Quantity" min="1">
        <button type="submit">Add Item</button>
    </form>

    <!-- <h2>Restock Items</h2>
    <form id="restockForm">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <select name="item_id" id="restock_item_id" required>
            <option value="">Select Item</option>
            
        </select>
        <input type="number" name="restock_quantity" required placeholder="Quantity" min="1">
        <button type="submit">Restock</button>
    </form> -->

    <h2>Issue Items</h2>
    <form id="issueForm">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <select name="item_id" id="item_id" required>
            <option value="">Select Item</option>
            <!-- Items will be loaded dynamically -->
        </select>
        <input type="number" name="issued_quantity" required placeholder="Quantity" min="1">
        <button type="submit">Issue</button>
    </form>
<!--
    <h2>Export Issued Items</h2>
    <form id="exportForm">
        <input type="date" name="start_date" required>
        <input type="date" name="end_date" required>
        <button type="submit">Export CSV</button>
    </form>
-->
<?php } ?>

<script>
    // Load Items for Restocking (Only for Admin)
    <?php if ($role === 'admin') { ?>
    fetch('fetch_items.php')
        .then(response => response.json())
        .then(data => {
            let itemSelect = document.getElementById('restock_item_id');
            data.forEach(item => {
                let option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.item_name;
                itemSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load items for restocking.');
        });
    <?php } ?>

    // Load Chart and Dropdown Data
    fetch('fetch_items.php')
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            // Load Chart Data
            let itemNames = data.map(item => item.item_name);
            let itemQuantities = data.map(item => item.quantity);

            let ctx = document.getElementById('inventoryChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: itemNames,
                    datasets: [{
                        label: 'Item Quantities',
                        data: itemQuantities,
                        backgroundColor: ['red', 'blue', 'green', 'yellow', 'purple'],
                        borderColor: '#000',
                        borderWidth: 1
                    }]
                }
            });

            // Low Stock Alert for items with quantity < 5
            data.forEach(item => {
                if (item.quantity < 5) {
                    alert(`Low stock alert: ${item.item_name} is below 5 units.`);
                }
            });

            // Load Items for Issuing (Only for Admin)
            <?php if ($role === 'admin') { ?>
            let itemSelect = document.getElementById('item_id');
            data.forEach(item => {
                let option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.item_name;
                itemSelect.appendChild(option);
            });
            <?php } ?>
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load data. Please try again.');
        });

    // Handle New Item Submission (Only for Admin)
    <?php if ($role === 'admin') { ?>
    document.getElementById('addItemForm').addEventListener('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);

        fetch('add_item.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            alert(data);
            location.reload(); // Refresh the page to reflect changes
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to add item. Please try again.');
        });
    });

    // Handle Issue Item Submission (Only for Admin)
    document.getElementById('issueForm').addEventListener('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);

        fetch('issue_item.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            alert(data);
            location.reload(); // Refresh the page to reflect changes
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to issue item. Please try again.');
        });
    });

    // Handle Export Issued Items as CSV
    document.getElementById('exportForm').addEventListener('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);

        fetch('export_issued_items.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.blob())
        .then(data => {
            // Create a downloadable link for the CSV
            const link = document.createElement('a');
            link.href = URL.createObjectURL(data);
            link.download = 'issued_items.csv';
            link.click();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to export data. Please try again.');
        });
    });
    <?php } ?>
</script>

<a href="logout.php">
    <button class="logout-btn">Logout</button>
</a>

</body>
</html>
