<?php 
error_reporting(E_ALL); // Enable all error reporting for debugging
include '../Includes/dbcon.php';
include '../Includes/session.php'; // Include session handling

// Automatically fetch the user ID from the session
$userId = $_SESSION['userId'] ?? null;

if (!$userId) {
    die("User ID not found in session.");
}

// Initialize an array to hold enrollment records
$enrollmentRecords = [];

// Fetch the user's enrollment data, including Start Time, End Time, Date, First name, Last name, and Class name
$enrollmentQuery = "
    SELECT 
        CONCAT(user_table.First_name, ' ', user_table.Last_name) AS Instructor,  -- Combine First and Last name into 'Instructor'
        laboratory_class.Class_name,  -- Class name from the laboratory_class table
        laboratory_class.Start_Time,  -- Start time of the lab class
        laboratory_class.End_Time,    -- End time of the lab class
        laboratory_class.Date,        -- Date of the lab class
        enrollment_table.Course_ID,
        enrollment_table.Year_ID,
        course_table.Course_name,
        year_table.Year_name
    FROM enrollment_table
    JOIN laboratory_class 
        ON enrollment_table.Course_ID = laboratory_class.Course_ID
        AND enrollment_table.Year_ID = laboratory_class.Year_ID
    JOIN course_table ON enrollment_table.Course_ID = course_table.Course_ID
    JOIN year_table ON enrollment_table.Year_ID = year_table.Year_ID
    JOIN user_table ON laboratory_class.User_ID = user_table.User_ID  -- Join with user_table to get first and last name
    WHERE enrollment_table.User_ID = ?  -- Keep this for the session User_ID filter
";

if ($stmt = mysqli_prepare($conn, $enrollmentQuery)) {
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $enrollmentRecords[] = $row;
    }
    mysqli_stmt_close($stmt);
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
  <link href="img/logo/kl.png" rel="icon">
  <title>Laboratory Schedule</title>
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
            <h1 class="h3 mb-0 text-gray-800"> Management Laboratory Schedule</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active">Laboratory Schedule</li>
            </ol>
          </div>

          <!-- Scheduling Table -->
          <div class="col-lg-12">
            <!-- First Table: Scheduling Entries -->
                    <div class="card mb-4">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                                    <h5 class="m-0 font-weight-bold text-primary">Laboratory Schedule</h5>
                                </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                                                <thead class="thead-light">
                                                <tr>
                                                    <th>Course Instructor</th>  <!-- Display Instructor column -->
                                                    <th>Course Title</th>
                                                    <th>Course Code</th>
                                                    <th>Academic Year</th>
                                                    <th>Scheduled Start Time</th>
                                                    <th>Scheduled End Time</th>
                                                    <th>Scheduled Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($enrollmentRecords as $enrollmentRow): ?>
                                                <tr>
                                                <td><?php echo ucwords(htmlspecialchars($enrollmentRow['Instructor'])); ?></td>

                                                    <td><?php echo htmlspecialchars($enrollmentRow['Class_name']); ?></td> <!-- Display Class Name -->
                                                    <td><?php echo htmlspecialchars($enrollmentRow['Course_name']); ?></td> <!-- Display Course Name -->
                                                    <td><?php echo htmlspecialchars($enrollmentRow['Year_name']); ?></td> <!-- Display Year Name -->
                                                    <td>
                                                        <?php
                                                        $startTime = strtotime($enrollmentRow['Start_Time']);
                                                        if ($startTime !== false) {
                                                            echo date("g:i A", $startTime); // 12-hour format with AM/PM
                                                        } else {
                                                            echo "Invalid Time";
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $endTime = strtotime($enrollmentRow['End_Time']);
                                                        if ($endTime !== false) {
                                                            echo date("g:i A", $endTime); // 12-hour format with AM/PM
                                                        } else {
                                                            echo "Invalid Time";
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $date = strtotime($enrollmentRow['Date']);
                                                        if ($date !== false) {
                                                            echo date("F j, Y", $date); // "Month Day, Year" format
                                                        } else {
                                                            echo "Invalid Date";
                                                        }
                                                        ?>
                                                    </td>
                                                <?php endforeach; ?>
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
