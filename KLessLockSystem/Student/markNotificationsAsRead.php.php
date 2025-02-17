<?php
// Error reporting for debugging
error_reporting(E_ALL);

// Include necessary files for database connection
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Fetch User ID from session
$user_id = $_SESSION['userId'] ?? null;
if (!$user_id) {
    die("User ID not found in session.");
}

// Fetch selected schedule ID (this should be passed from the client-side)
$schedule_id = $_POST['schedule_id'] ?? null;
if (!$schedule_id) {
    die("Schedule ID not provided.");
}

// Update notifications to "viewed" for the logged-in user and the selected schedule
$query = "
    UPDATE scheduling_table
    SET Status_ID = (
        SELECT Status_ID
        FROM status_table
        WHERE Status_Name = 'viewed'
    )
    WHERE User_ID = ? AND Schedule_ID = ? AND (Status_ID = 4 OR Status_ID = (
        SELECT Status_ID
        FROM status_table
        WHERE Status_Name = 'approved'
    ))";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $schedule_id);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Notifications marked as viewed for the selected schedule.']);
} else {
    echo json_encode(['error' => 'Failed to update notifications.']);
}

$stmt->close();
$conn->close();
?>
