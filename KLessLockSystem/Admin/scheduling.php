<?php 
error_reporting(E_ALL); // Enable all error reporting for debugging
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Function to create a scheduling entry
function createSchedulingEntry($conn) {
    $user_id = $_POST['user_id'];
    $sched_time = $_POST['sched_time'];
    $end_time = $_POST['end_time'];
    $date = $_POST['date'];
    $label = $_POST['label'];
    $status_id = $_POST['status_id'];

    $query = "INSERT INTO scheduling_table (User_ID, Sched_time, End_time, Date, Label, Status_ID) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        die('Error preparing query: ' . $conn->error);
    }

    $stmt->bind_param("issssi", $user_id, $sched_time, $end_time, $date, $label, $status_id);
    return $stmt->execute();
}

// Function to update a scheduling entry
function updateSchedulingEntry($conn) {
    if (isset($_POST['scheduleId'])) {
        $schedule_id = $_POST['scheduleId'];
        $user_id = $_POST['user_id'];
        $sched_time = $_POST['sched_time'];
        $end_time = $_POST['end_time'];
        $date = $_POST['date'];
        $label = $_POST['label'];
        $status_id = $_POST['status_id'];

        $query = "UPDATE scheduling_table 
                  SET User_ID = ?, Sched_time = ?, End_time = ?, Date = ?, Label = ?, Status_ID = ? 
                  WHERE Schedule_ID = ?";
        $stmt = $conn->prepare($query);

        if ($stmt === false) {
            die('Error preparing query: ' . $conn->error);
        }

        $stmt->bind_param("issssii", $user_id, $sched_time, $end_time, $date, $label, $status_id, $schedule_id);
        return $stmt->execute();
    }
    return false;
}

// Function to delete a scheduling entry
function deleteSchedulingEntry($conn, $scheduleId) {
    $query = "DELETE FROM scheduling_table WHERE Schedule_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $scheduleId);
    return $stmt->execute();
}

// Function to edit scheduling entry data
function editSchedulingEntry($conn, $scheduleId) {
    $query = "SELECT * FROM scheduling_table WHERE Schedule_ID=?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $scheduleId);
        $stmt->execute();
        $result = $stmt->get_result();
        $schedulingData = $result->fetch_assoc();
        $stmt->close();
        return $schedulingData;
    }
    return null;
}

// Handle form actions
$statusMsg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit']) && createSchedulingEntry($conn)) {
        $statusMsg = "<div class='alert alert-success'>Scheduling Entry Created Successfully!</div>";
    } elseif (isset($_POST['edit']) && updateSchedulingEntry($conn)) {
        $statusMsg = "<div class='alert alert-success'>Scheduling Entry Updated Successfully!</div>";
    } else {
        $statusMsg = "<div class='alert alert-danger'>Error processing request.</div>";
    }
}

// Handle approve, disapprove, or delete actions
if (isset($_GET['action']) && isset($_GET['Schedule_ID'])) {
    $scheduleId = $_GET['Schedule_ID'];
    $action = $_GET['action'];

    if ($action == 'approve') {
        $status_id = 4; // Approved
        if (updateSchedulingEntryStatus($conn, $scheduleId, $status_id)) {
            $statusMsg = "<div class='alert alert-success'>Scheduling Entry Approved Successfully!</div>";
        } else {
            $statusMsg = "<div class='alert alert-danger'>Error approving scheduling entry.</div>";
        }
    } elseif ($action == 'disapprove') {
        $status_id = 5; // Disapproved
        if (updateSchedulingEntryStatus($conn, $scheduleId, $status_id)) {
            $statusMsg = "<div class='alert alert-warning'>Scheduling Entry Disapproved Successfully!</div>";
        } else {
            $statusMsg = "<div class='alert alert-danger'>Error disapproving scheduling entry.</div>";
        }
    } elseif ($action == 'delete' && deleteSchedulingEntry($conn, $scheduleId)) {
        $statusMsg = "<div class='alert alert-success'>Scheduling Entry Deleted Successfully!</div>";
    } else {
        $statusMsg = "<div class='alert alert-danger'>Error processing action.</div>";
    }
}

// Function to update the status of a scheduling entry
function updateSchedulingEntryStatus($conn, $scheduleId, $status_id) {
    $query = "UPDATE scheduling_table SET Status_ID=? WHERE Schedule_ID=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $status_id, $scheduleId);
    return $stmt->execute();
}

// Fetch scheduling data for editing
$schedulingData = [];
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['Schedule_ID'])) {
    $scheduleId = $_GET['Schedule_ID'];
    $schedulingData = editSchedulingEntry($conn, $scheduleId);
}

$schedulingTodays = "
    SELECT 
        s.*, 
        st.Status_Name,
        u.First_name,
        u.Last_name,
        u.School_ID  -- Add School_ID to the query
    FROM scheduling_table s
    INNER JOIN status_table st ON s.Status_ID = st.Status_ID
    INNER JOIN user_table u ON s.User_ID = u.User_ID
    WHERE (st.Status_Name = 'Approved' OR s.Status_ID = 4)
    AND DATE(s.Date) = CURDATE()
";
$schedulingTodaysResult = mysqli_query($conn, $schedulingTodays);
if (!$schedulingTodaysResult) {
    die("Query failed: " . mysqli_error($conn));
}



// Fetch all scheduling entries
$schedulingQuery = "
    SELECT 
        s.*, 
        st.Status_Name,
        u.First_name,
        u.Last_name,
        u.User_ID,
        u.School_ID  -- Add School_ID to the query
    FROM scheduling_table s
    INNER JOIN status_table st ON s.Status_ID = st.Status_ID
    INNER JOIN user_table u ON s.User_ID = u.User_ID
    WHERE st.Status_Name != 'Approved' AND s.Status_ID != 4
";

$schedulingResult = mysqli_query($conn, $schedulingQuery);
if (!$schedulingResult) {
    die("Query failed: " . mysqli_error($conn));
}


?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
  <link href="img/logo/qc.png" rel="icon">
  <title>Scheduling</title>
  
</head>
<body id="page-top">
  <div id="wrapper">
    <?php include "Includes/sidebar.php"; ?>
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <?php include "Includes/topbar.php"; ?>
        <div class="container-fluid" id="container-wrapper">
          <!-- Page Header -->
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Scheduling Lab Classes Approval </h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active">Scheduling Lab Classes Approval</li>
            </ol>
          </div>

          <!-- Scheduling Table -->
          <div class="col-lg-12">
    <!-- First Table: Scheduling Entries -->
      <div class="card mb-4">
                                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                        <h5 class="m-0 font-weight-bold text-primary">Management Schedule</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                                                <thead class="thead-light">
                                                <tr>
            <th style="font-size: 15px;">Student Name</th>
            <th style="font-size: 15px;">School ID</th>
            <th style="font-size: 15px;">Label</th>
            <th style="font-size: 15px;">Start Time</th>
            <th style="font-size: 15px;">End Time</th>
            <th style="font-size: 15px;">Date</th>
            <th style="font-size: 15px;">Status</th>
            <th style="font-size: 15px;">Actions</th>
            <th style="font-size: 15px;">Approve / Disapprove</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($schedulingResult)): ?>
        <tr>
            <td style="font-size: 14px;">
                <?php 
                    echo ucfirst(strtolower(htmlspecialchars($row['First_name']))) . " " . ucfirst(strtolower(htmlspecialchars($row['Last_name'])));
                ?>
            </td>
            <td style="font-size: 14px;">
             <?php echo htmlspecialchars($row['School_ID']); ?> <!-- Display School_ID -->
            </td>

            <td style="font-size: 14px;">
                <?php 
                    echo ucwords(strtolower(htmlspecialchars($row['Label'])));
                ?>
            </td>
            <td style="font-size: 14px;">
                <?php 
                $startTime = strtotime($row['Sched_time']);
                echo $startTime !== false ? date("g:i A", $startTime) : "Invalid Time"; 
                ?>
            </td>
            <td style="font-size: 14px;">
                <?php 
                $endTime = strtotime($row['End_time']);
                echo $endTime !== false ? date("g:i A", $endTime) : "Invalid Time"; 
                ?>
            </td>
            <td style="font-size: 14px;">
                <?php 
                $date = strtotime($row['Date']);
                echo $date !== false ? date("F j, Y", $date) : "Invalid Date"; 
                ?>
            </td>
            <td style="font-size: 14px;"><?php echo htmlspecialchars($row['Status_Name']); ?></td>


            <td>
                <a href="?action=edit&Schedule_ID=<?php echo $row['Schedule_ID']; ?>" class="btn btn-info btn-sm">
                    <i class="fas fa-edit"></i>
                </a>
                <a href="?action=delete&Schedule_ID=<?php echo $row['Schedule_ID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this scheduling entry?');">
                    <i class="fas fa-trash"></i>
                </a>
            </td>
            <td style="font-size: 14px;">
                <?php if ($row['Status_Name'] === 'Pending'): ?>
                    <!-- Show Approve and Disapprove buttons for pending status -->
                    <button class="btn btn-success btn-sm" id="approve-btn-<?php echo $row['Schedule_ID']; ?>" onclick="handleAction('approve', <?php echo $row['Schedule_ID']; ?>)">
                    <i class="fas fa-check"></i> Approve
                    </button>
                    <button class="btn btn-warning btn-sm" id="disapprove-btn-<?php echo $row['Schedule_ID']; ?>" onclick="handleAction('disapprove', <?php echo $row['Schedule_ID']; ?>)">
                    <i class="fas fa-times"></i> Disapprove
                    </button>
                <?php elseif ($row['Status_Name'] === 'Disapproved'): ?>
                    <!-- Show only the Delete button for disapproved status -->
                    <button class="btn btn-danger btn-sm" id="delete-btn-<?php echo $row['Schedule_ID']; ?>" onclick="handleAction('delete', <?php echo $row['Schedule_ID']; ?>)">
                    <i class="fas fa-trash"></i> Delete
                    </button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>


    <!-- Second Table: Another Example -->
    
           <div class="card mb-4">
                                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                        <h5 class="m-0 font-weight-bold text-primary">Today's Schedule</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                                                <thead class="thead-light">
                                                    <tr>
                            <th style="font-size: 15px;">User Name</th>
                            <th style="font-size: 15px;">School ID</th>
                            <th style="font-size: 15px;">Label</th>
                            <th style="font-size: 15px;">Start Time</th>
                            <th style="font-size: 15px;">End Time</th>
                            <th style="font-size: 15px;">Date</th>
                            <th style="font-size: 15px;">Status</th>
                    
                        </tr>
                    </thead>
                    <tbody>
                            <?php while ($row = mysqli_fetch_assoc($schedulingTodaysResult)): ?>
                            <tr>
                            <td style="font-size: 14px;">
                                <?php 
                                    echo ucfirst(strtolower(htmlspecialchars($row['First_name']))) . " " . ucfirst(strtolower(htmlspecialchars($row['Last_name'])));
                                ?>
                            </td>
                            <td style="font-size: 14px;">
                                <?php 
                                echo "" . htmlspecialchars($row['School_ID']) . "<br>"; // Display School ID
                                ?>
                            </td>


                            <td style="font-size: 14px;">
                                <?php 
                                    echo ucwords(strtolower(htmlspecialchars($row['Label'])));
                                ?>
                            </td>


                                <td style="font-size: 14px;">
                                <?php 
                                $startTime = strtotime($row['Sched_time']);
                                echo $startTime !== false ? date("g:i A", $startTime) : "Invalid Time"; 
                                ?>
                                </td>
                                <td style="font-size: 14px;">
                                <?php 
                                $endTime = strtotime($row['End_time']);
                                echo $endTime !== false ? date("g:i A", $endTime) : "Invalid Time"; 
                                ?>
                                </td>
                                <td style="font-size: 14px;">
                                <?php 
                                $date = strtotime($row['Date']);
                                echo $date !== false ? date("F j, Y", $date) : "Invalid Date"; 
                                ?>
                                </td>
                                <td style="font-size: 14px;"><?php echo htmlspecialchars($row['Status_Name']); ?></td>
                                
                            </tr>
                            <?php endwhile; ?>
                    </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
  <!-- Scroll to Top Button -->
 
  <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

  <!-- Action Handling Script -->
  <script>
    function handleAction(action, scheduleId) {
      const approveBtn = document.getElementById(`approve-btn-${scheduleId}`);
      const disapproveBtn = document.getElementById(`disapprove-btn-${scheduleId}`);

      let confirmationMessage = action === "approve"
        ? "Are you sure you want to approve this scheduling entry?"
        : "Are you sure you want to disapprove this scheduling entry?";

      if (confirm(confirmationMessage)) {
        if (action === "approve") {
          approveBtn.disabled = true;
          disapproveBtn.disabled = true;
        } else if (action === "disapprove") {
          disapproveBtn.disabled = true;
          approveBtn.disabled = true;
        }
        window.location.href = `?action=${action}&Schedule_ID=${scheduleId}`;
      }
    }
  </script>

  <!-- Scripts -->
  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
  <script>
    $(document).ready(function () {
      $('#dataTable').DataTable({
        "order": [[0, 'desc']]
      });
      $('#dataTableHover').DataTable({
        "order": [[0, 'desc']]
      });
    });
  </script>
</body>

</html>
