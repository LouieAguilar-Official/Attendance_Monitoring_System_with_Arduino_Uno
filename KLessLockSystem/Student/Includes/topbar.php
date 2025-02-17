<?php 
// Error reporting

// Fetch User ID from session
$user_id = $_SESSION['userId'] ?? null;
if (!$user_id) {
    die("User ID not found in session.");
}

// Include necessary files for database connection
// (Make sure you have a connection to the database here, e.g., $conn = mysqli_connect(...) )

$currentDate = date('Y-m-d'); // Current date

// Updated query to fetch scheduling where Status_ID is 4 or Status_Name is 'approved'
$schedulingQueryNotification = "
    SELECT 
        s.Schedule_ID, 
        s.User_ID, 
        s.Label, 
        s.Sched_time, 
        s.End_time, 
        s.Date, 
        s.List, 
        s.Status_ID, 
        u.School_ID, 
        st.Status_Name
    FROM 
        scheduling_table s
    LEFT JOIN 
        user_table u ON s.User_ID = u.User_ID
    LEFT JOIN 
        status_table st ON s.Status_ID = st.Status_ID
    WHERE 
        s.User_ID = ? AND (s.Status_ID = 4 OR st.Status_Name = 'approved') AND s.Date = '$currentDate'
    ORDER BY 
        s.Date ASC, s.Sched_time ASC";

// Prepare and execute the query to fetch schedules for the logged-in user
$stmt = $conn->prepare($schedulingQueryNotification);
$stmt->bind_param("i", $user_id); // Bind the user ID
$stmt->execute();
$schedulingQueryNotificationResult = $stmt->get_result();

// Check if there are any approved schedules
$notifications = [];
$approvedCount = 0; // Variable to count approved notifications
if ($schedulingQueryNotificationResult->num_rows > 0) {
    while ($row = $schedulingQueryNotificationResult->fetch_assoc()) {
        // Count notifications with Status_ID = 4 or Status_Name 'approved'
        if ($row['Status_ID'] == 4 || $row['Status_Name'] == 'approved') {
            $approvedCount++;
        }

        // Create a notification for each schedule
        $notification = [
            'date' => $row['Date'],
            'message' => 'Your scheduling request for ' . $row['Label'] . ' has been ' . $row['Status_Name'] . '.',
            'status' => $row['Status_Name'],
        ];
        $notifications[] = $notification;
    }
} else {
    // No notifications if there are no approved schedules
    $notifications[] = [
        'date' => $currentDate,
        'message' => 'No approved schedules for today.',
        'status' => 'No Action'
    ];
}

// Prepare and execute the query to fetch user information
$stmt = $conn->prepare("SELECT * FROM user_table WHERE User_ID = ?");
$stmt->bind_param("i", $user_id); // Bind the user ID
$stmt->execute();
$rs = $stmt->get_result();
$num = $rs->num_rows;

if ($num > 0) {
    $rows = $rs->fetch_assoc();
    $fullName = $rows['First_name'] . " " . $rows['Last_name'];
} else {
    $fullName = "User not found"; // Handle case when user is not found
}

$stmt->close(); // Close the statement
?>

<nav class="navbar navbar-expand navbar-light bg-gradient-primary topbar mb-4 static-top">
    <button id="sidebarToggleTop" class="btn btn-link rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>
    <div class="text-white big" style="margin-left:100px;"><b></b></div>


    <ul class="navbar-nav ml-auto">
        <!-- Notification Dropdown -->


        <li class="nav-item dropdown no-arrow mx-1">

        
            <a class="nav-link dropdown-toggle" href="#" id="notificationsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onclick="resetNotificationCount()">
                <i class="fas fa-bell fa-fw"></i>
                <!-- Notification Badge -->
                <span class="badge badge-danger badge-counter" id="notification-count"><?php echo $approvedCount; ?></span>
            </a>




            
            <!-- Notification Menu -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="notificationsDropdown">
                <h6 class="dropdown-header">
                    Notifications
                </h6>
                <?php foreach ($notifications as $notification): ?>



                    <a class="dropdown-item d-flex align-items-center" href="#">
                        <div class="mr-3">
                            <div class="icon-circle <?php echo ($notification['status'] == 'approved') ? 'bg-success' : 'bg-warning'; ?>">
                                <i class="fas fa-check text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500"><?php echo $notification['date']; ?></div>
                            <span class="font-weight-bold"><?php echo $notification['message']; ?></span>
                        </div>
                    </a>



                <?php endforeach; ?>



                <a class="dropdown-item text-center small text-gray-500" href="#">Show All Notifications</a>
            </div>
        </li>

        <!-- User Dropdown -->
        <div class="topbar-divider d-none d-sm-block"></div>
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <img class="img-profile rounded-circle" src="img/user-icn.png" style="max-width: 60px">
                <span class="ml-2 d-none d-lg-inline text-white small"><b>Welcome Student | <?php echo $fullName; ?></b></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <!-- Profile Link -->
                <a class="dropdown-item" href="profile.php">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profile
                </a>
                <!-- Settings Link -->
                <a class="dropdown-item" href="#">
                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                    Settings
                </a>
                <!-- Activity Log Link -->
                <a class="dropdown-item" href="#">
                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                    Activity Log
                </a>
                <div class="dropdown-divider"></div>
                <!-- Logout Link -->
                <a class="dropdown-item" href="../Homepage/homepage.php">
                    <i class="fas fa-power-off fa-fw mr-2 text-danger"></i>
                    Logout
                </a>
            </div>
        </li>
    </ul>
</nav>

<script>
    // JavaScript function to reset notification count
    function resetNotificationCount() {
        // Reset the notification badge counter to 0
        document.getElementById("notification-count").innerText = 0;

        // Optionally, you can also send an AJAX request to mark notifications as read in the database if required
        // Example:
        // fetch('markNotificationsAsRead.php')
        //     .then(response => response.json())
        //     .then(data => console.log('Notifications marked as read'))
        //     .catch(error => console.error('Error:', error));
    }
</script>
