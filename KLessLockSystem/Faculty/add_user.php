<?php
error_reporting(E_ALL);
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Assuming the current logged-in faculty ID is stored in a session variable
$userId = $_SESSION['userId'];

// Fetch enrollment data for the logged-in faculty, including School_ID, First_name, Last_name, Contact_num, and Class_name
$enrollmentQuery = "
    SELECT u.School_ID, u.First_name, u.Last_name, u.Contact_num, 
           c.Course_name, y.Year_name, s.Section_name, l.Class_name
    FROM enrollment_table e
    JOIN course_table c ON e.Course_ID = c.Course_ID 
    JOIN year_table y ON e.Year_ID = y.Year_ID 
    JOIN section_table s ON e.Section_ID = s.Section_ID 
    JOIN user_table u ON e.User_ID = u.User_ID
    JOIN laboratory_class l ON e.LabClass_ID = l.Labclass_ID
    WHERE l.User_ID = '$userId'"; // Filter by logged-in faculty's User_ID

// Execute the query
$result = mysqli_query($conn, $enrollmentQuery);
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
            <h1 class="h3 mb-0 text-gray-800"> Student</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active">All Student</li>
            </ol>
          </div>

          <!-- Scheduling Table -->
          <div class="col-lg-12">
            <!-- First Table: Scheduling Entries -->
                    <div class="card mb-4">
                                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                        <h5 class="m-0 font-weight-bold text-primary">My Student</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                                                <thead class="thead-light">
                                                <tr>
                                                <th>Student ID</th>
                                                <th>First Name</th>
                                                <th>Last Name</th>
                                                <th>Phone Number</th>
                                                <th>Grade Level</th>
                                                <th>Program</th>
                                                <th>Academic Year</th>
                                                <th>Section</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php 
                                                // Display the students enrolled in the laboratory classes of the logged-in faculty
                                                while ($enrollmentRow = mysqli_fetch_assoc($result)) { ?>
                                                    <tr>
                                                        <td><?php echo $enrollmentRow['School_ID']; ?></td>
                                                        <td>
                                                            <?php echo ucwords($enrollmentRow['First_name']); ?>
                                                        </td>
                                                        <td>
                                                            <?php echo ucwords($enrollmentRow['Last_name']); ?>
                                                        </td>   


                                                        <td><?php echo $enrollmentRow['Contact_num']; ?></td>
                                                        <td><?php echo $enrollmentRow['Class_name']; ?></td>
                                                        <td><?php echo $enrollmentRow['Course_name']; ?></td>
                                                        <td><?php echo $enrollmentRow['Year_name']; ?></td>
                                                        <td><?php echo $enrollmentRow['Section_name']; ?></td>
                                                    </tr>
                                                <?php } ?>
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
