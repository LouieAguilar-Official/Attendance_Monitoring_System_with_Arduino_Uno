<?php
error_reporting(E_ALL);
include '../Includes/dbcon.php';
include '../Includes/session.php';

$userId = $_SESSION['userId'] ?? null;

if (!$userId) {
    die("User ID not found in session.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fetch_data'])) {
    $fetchQuery = "
        SELECT 
            ft.User_ID, 
            rt.Record_ID, 
            rt.Time_and_Date
        FROM record_table rt
        INNER JOIN fingerprint_table ft ON rt.Fingerprint_ID = ft.Fingerprint_ID
        WHERE ft.User_ID = ?
    ";

    if ($stmtFetch = mysqli_prepare($conn, $fetchQuery)) {
        mysqli_stmt_bind_param($stmtFetch, 'i', $userId);
        mysqli_stmt_execute($stmtFetch);
        $resultFetch = mysqli_stmt_get_result($stmtFetch);

        while ($record = mysqli_fetch_assoc($resultFetch)) {
            $recordID = $record['Record_ID'];
            $timeAndDate = $record['Time_and_Date'];
            $datePart = date('Y-m-d', strtotime($timeAndDate));
            $timePart = date('H:i:s', strtotime($timeAndDate));

            $labclassQuery = "
                SELECT 
                    lc.Labclass_ID, 
                    lc.Start_Time, 
                    lc.End_Time 
                FROM laboratory_class lc
                INNER JOIN enrollment_table et ON lc.Labclass_ID = et.Labclass_ID
                WHERE et.User_ID = ? 
                AND lc.Date = ?
            ";

            if ($stmtLabclass = mysqli_prepare($conn, $labclassQuery)) {
                mysqli_stmt_bind_param($stmtLabclass, 'is', $userId, $datePart);
                mysqli_stmt_execute($stmtLabclass);
                $resultLabclass = mysqli_stmt_get_result($stmtLabclass);

                while ($class = mysqli_fetch_assoc($resultLabclass)) {
                    $labclassID = $class['Labclass_ID'];
                    $startTime = $class['Start_Time'];
                    $endTime = $class['End_Time'];

                    if ($timePart >= $startTime && $timePart <= $endTime) {
                        $insertQuery = "
                            INSERT INTO attendance_table (User_ID, Labclass_ID, Record_ID, Attendance_Date, Status_ID)
                            SELECT ?, ?, ?, ?, 6
                            WHERE NOT EXISTS (
                                SELECT 1 FROM attendance_table 
                                WHERE User_ID = ? AND Labclass_ID = ? AND Record_ID = ?
                            )
                        ";

                        if ($stmtInsert = mysqli_prepare($conn, $insertQuery)) {
                            mysqli_stmt_bind_param($stmtInsert, 'iiissii', $userId, $labclassID, $recordID, $timeAndDate, $userId, $labclassID, $recordID);
                            mysqli_stmt_execute($stmtInsert);
                            mysqli_stmt_close($stmtInsert);
                        }
                    }
                }
                mysqli_stmt_close($stmtLabclass);
            }
        }
        mysqli_stmt_close($stmtFetch);
    }
}

$fetchAttendanceQuery = "
    SELECT 
        a.Attendance_ID,
        u.School_ID,
        u.First_name,
        u.Last_name,
        a.Labclass_ID,
        lc.Class_name,
        a.Attendance_Date,
        s.Status_Name
    FROM attendance_table a
    INNER JOIN user_table u ON a.User_ID = u.User_ID
    INNER JOIN status_table s ON a.Status_ID = s.Status_ID
    INNER JOIN laboratory_class lc ON a.Labclass_ID = lc.Labclass_ID
    WHERE a.User_ID = ?
";

$attendanceRecords = [];
if ($stmtFetchAttendance = mysqli_prepare($conn, $fetchAttendanceQuery)) {
    mysqli_stmt_bind_param($stmtFetchAttendance, 'i', $userId);
    mysqli_stmt_execute($stmtFetchAttendance);
    $resultAttendance = mysqli_stmt_get_result($stmtFetchAttendance);

    while ($attendance = mysqli_fetch_assoc($resultAttendance)) {
        $attendanceRecords[] = $attendance;
    }
    mysqli_stmt_close($stmtFetchAttendance);
}

// Fetch all students from user_table for User List
$studentQuery = "SELECT u.*, ut.Type_name 
                 FROM user_table u 
                 JOIN usertype_table ut ON u.Usertype_ID = ut.Usertype_ID
                 ORDER BY u.User_ID DESC"; // Order by User_ID in descending order
$studentResult = $conn->query($studentQuery);
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
  <link href="img/logo/kl.png" rel="icon">
  <title>Attendance</title>
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
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                                    <h5 class="m-0 font-weight-bold text-primary">Attendance</h5>
                                </div>
                                    <div class="card-body">
                                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                                        <form method="POST" action="">
                                            <button type="submit" name="fetch_data" class="btn btn-primary">Fetch Data</button>
                                        </form>
                                    </div>
                                        <div class="table-responsive">
                                            <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                                                <thead class="thead-light">
                                                <tr>
                                                    <th>Student Name</th>
                                                    <th>Student ID</th>
                                                    <th>Subject Name</th>
                                                    <th>Date of Attendance</th>
                                                    <th>Attendance Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (count($attendanceRecords) > 0): ?>
                                                    <?php foreach ($attendanceRecords as $record): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars(ucwords(strtolower($record['First_name']))) . ' ' . htmlspecialchars(ucwords(strtolower($record['Last_name']))); ?></td>
                                                            <td><?php echo htmlspecialchars($record['School_ID']); ?></td>
                                                            <td><?php echo htmlspecialchars($record['Class_name']); ?></td>
                                                            <td><?php echo date('F j, Y', strtotime($record['Attendance_Date'])); ?></td>
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
