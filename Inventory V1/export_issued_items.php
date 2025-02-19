<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access.");
}

include "includes/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $query = "SELECT * FROM issued_items WHERE issue_date BETWEEN ? AND ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();

    $result = $stmt->get_result();
    $output = fopen("php://output", "w");
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=issued_items.csv");

    $columns = ["Item Name", "Quantity", "Issue Date"];
    fputcsv($output, $columns);

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }

    fclose($output);
}
?>
