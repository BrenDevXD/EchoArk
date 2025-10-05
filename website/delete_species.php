<?php
session_start();

// Security: Check if the user is logged in AND their role is 'admin'
if (!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== 'admin') {
    header("location: index.php");
    exit;
}

// Check if the 'id' parameter is set in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: admin_dashboard.php?status=error&message=" . urlencode("No species ID provided."));
    exit;
}

// Get the species ID from the URL
$species_id = intval($_GET['id']);

// Database Connection
$servername = "localhost";
$username = "root";
$password_db = "";
$dbname = "echoark_base";

$conn = new mysqli($servername, $username, $password_db, $dbname);
if ($conn->connect_error) {
    header("location: admin_dashboard.php?status=error&message=" . urlencode("Database connection failed."));
    exit;
}

// Prepare and execute the deletion query
// Use a prepared statement to prevent SQL injection attacks
$stmt = $conn->prepare("DELETE FROM species WHERE id = ?");
$stmt->bind_param("i", $species_id);

$message = '';
$status = 'error';

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $message = "Species deleted successfully!";
        $status = 'success';
    } else {
        $message = "No species found with that ID.";
    }
} else {
    $message = "Error deleting species: " . $stmt->error;
}

$stmt->close();
$conn->close();

// Redirect back to the admin dashboard with a status message
header("location: admin_dashboard.php?status=" . $status . "&message=" . urlencode($message));
exit();
?>