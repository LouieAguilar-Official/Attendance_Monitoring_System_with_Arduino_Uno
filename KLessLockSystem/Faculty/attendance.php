<?php 
error_reporting(E_ALL);
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Fetch the User ID from the session
$userId = $_SESSION['userId'] ?? null;

if (!$userId) {
    die("User ID not found in session.");
}

// Initialize an array to hold attendance records
$attendanceRecords = [];

// Fetch data regardless of request method
function fetchAttendanceData($conn, $userId) {
    $attendanceRecords = [];

    // Fetch attendance records with School_ID, Class_Name, and Status_Name
    $fetchAttendanceQuery = "
        SELECT 
            a.Attendance_ID,
            u.School_ID,
            lc.Class_Name,
            a.Attendance_Date,
            s.Status_Name
        FROM 
            attendance_table a
        INNER JOIN 
            user_table u ON a.User_ID = u.User_ID
        INNER JOIN 
            laboratory_class lc ON a.Labclass_ID = lc.Labclass_ID
        INNER JOIN 
            status_table s ON a.Status_ID = s.Status_ID
        WHERE 
            lc.User_ID = ?;
    ";

    if ($stmt = mysqli_prepare($conn, $fetchAttendanceQuery)) {
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        // Store fetched records in the array
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $attendanceRecords[] = $row;
            }
        }
        mysqli_stmt_close($stmt);
    }

    return $attendanceRecords;
}

// Fetch attendance records for the user
$attendanceRecords = fetchAttendanceData($conn, $userId);
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
  <title>Entry</title>
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
            <h1 class="h3 mb-0 text-gray-800"> Attendance Report</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active">All Attendance</li>
            </ol>
          </div>

          <!-- Scheduling Table -->
          <div class="col-lg-12">
            <!-- First Table: Scheduling Entries -->
                    <div class="card mb-4">
                                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                        <h5 class="m-0 font-weight-bold text-primary">Attendance Sheet</h6>
                                    </div>
                                    <div class="card-body">
                                        
                                        <div class="table-responsive">
                                            <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                                                <thead class="thead-light">
                                                <tr>
                                                <th>Student ID</th>
                                                <th>Course Title</th>
                                                <th>Attendance Date</th>
                                                <th>Attendance Status</th>


                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php if (count($attendanceRecords) > 0): ?>
                                <?php foreach ($attendanceRecords as $record): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($record['School_ID']); ?></td>
                                        <td><?php echo htmlspecialchars($record['Class_Name']); ?></td>
                                        <td>
                                            <?php 
                                                // Assuming the date is in 'YYYY-MM-DD' format
                                                $formattedDate = date("F j, Y", strtotime($record['Attendance_Date']));
                                                echo htmlspecialchars($formattedDate);
                                            ?>
                                        </td>


                                        <td><?php echo htmlspecialchars($record['Status_Name']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No records found.</td>
                                </tr>
                            <?php endif; ?>
                                            </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
          </div>
          
          <!-- Scroll to Top Button -->
          <a class="scroll-to-top rounded" href="#page-top">
            <i class="fas fa-angle-up"></i>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
  <script>
    $(document).ready(function () {
      // Initialize DataTables
      $('#dataTableHover').DataTable({
        "order": [[0, 'desc']] // Sorting by first column (Record_ID) in descending order
      });
    });
  </script>
</body>

</html>
