<?php 
error_reporting(E_ALL); // Enable all error reporting for debugging
include '../Includes/dbcon.php';
include '../Includes/session.php'; // Include session handling

// Initialize an array to hold attendance records
$attendanceRecords = [];

// Fetch all attendance records with additional details
$fetchAttendanceQuery = "
    SELECT 
        a.Attendance_ID, 
        u.School_ID, 
        l.Class_name, 
        a.Attendance_Date, 
        s.Status_Name 
    FROM attendance_table a
    INNER JOIN user_table u ON a.User_ID = u.User_ID
    INNER JOIN Laboratory_Class l ON a.Labclass_ID = l.Labclass_ID
    INNER JOIN status_table s ON a.Status_ID = s.Status_ID
";

if ($stmtFetchAttendance = mysqli_prepare($conn, $fetchAttendanceQuery)) {
    mysqli_stmt_execute($stmtFetchAttendance);
    $attendanceResult = mysqli_stmt_get_result($stmtFetchAttendance);

    // Check if we got a valid mysqli_result
    if ($attendanceResult && mysqli_num_rows($attendanceResult) > 0) {
        while ($attendanceRow = mysqli_fetch_assoc($attendanceResult)) {
            $attendanceRecords[] = $attendanceRow; // Store fetched records in the array
        }
    }
    mysqli_stmt_close($stmtFetchAttendance);
} else {
    echo "Error preparing fetch attendance query: " . mysqli_error($conn);
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
                                                    <th>School ID</th>
                                                    <th>Class Name</th>
                                                    <th>Attendance Date</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (count($attendanceRecords) > 0): ?>
                                                    <?php foreach ($attendanceRecords as $record): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($record['School_ID']); ?></td>
                                                            <td><?php echo htmlspecialchars($record['Class_name']); ?></td>
                                                            <td><?php echo ucwords(date("F j, Y", strtotime($record['Attendance_Date']))); ?></td>

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
