<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Query to fetch class and section information
// $query = "SELECT tblclass.className, tblclassarms.classArmName 
// FROM tblclassteacher
// INNER JOIN tblclass ON tblclass.Id = tblclassteacher.classId
// INNER JOIN tblclassarms ON tblclassarms.Id = tblclassteacher.classArmId
// WHERE tblclassteacher.Id = '$_SESSION[userId]'";
//
// $rs = $conn->query($query);
// $num = $rs->num_rows;
// $rrw = $rs->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <link href="img/logo/qc.png" rel="icon">
  <title>Dashboard</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
</head>

<body id="page-top">
  <div id="wrapper">
    <!-- Sidebar -->
    <?php include "Includes/sidebar.php"; ?>
    <!-- Sidebar -->
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <!-- TopBar -->
        <?php include "Includes/topbar.php"; ?>
        <!-- Topbar -->
        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Administrator Dashboard</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
          </div>

          <div class="row mb-3">
            <!-- Users Card -->
            <?php 
            $query1 = mysqli_query($conn, "SELECT * FROM user_table WHERE Usertype_ID");
            $user = mysqli_num_rows($query1);
            ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Users</div>
                      <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><?php echo $user; ?></div>
                      <div class="mt-2 mb-0 text-muted text-xs">
                        <!-- Placeholder for future data -->
                      </div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-users fa-2x text-info"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Administrator Card -->
            <?php 
            $query1 = mysqli_query($conn, "SELECT * FROM user_table WHERE Usertype_ID = 1");
            $admin = mysqli_num_rows($query1);
            ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Administrator</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $admin; ?></div>
                      <div class="mt-2 mb-0 text-muted text-xs">
                        <!-- Placeholder for future data -->
                      </div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-user-shield fa-2x text-primary"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Faculty Card -->
            <?php 
            $query1 = mysqli_query($conn, "SELECT * FROM user_table WHERE Usertype_ID = 2");
            $faculty = mysqli_num_rows($query1);
            ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Faculty</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $faculty; ?></div>
                      <div class="mt-2 mb-0 text-muted text-xs">
                        <!-- Placeholder for future data -->
                      </div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-chalkboard-teacher fa-2x text-success"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Student Card -->
            <?php 
            $query1 = mysqli_query($conn, "SELECT * FROM user_table WHERE Usertype_ID = 3");
            $student = mysqli_num_rows($query1);
            ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Students</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $student; ?></div>
                      <div class="mt-2 mb-0 text-muted text-xs">
                        <!-- Placeholder for future data -->
                      </div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-user fa-2x text-secondary"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Class Card -->
            <?php 
            $query1 = mysqli_query($conn, "SELECT * FROM laboratory_class");
            $class = mysqli_num_rows($query1);
            ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Classes</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $class; ?></div>
                      <div class="mt-2 mb-0 text-muted text-xs">
                        <!-- Placeholder for future data -->
                      </div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-chalkboard-teacher fa-2x text-danger"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Section Card -->
            <?php 
            $query1 = mysqli_query($conn, "SELECT * FROM section_table");
            $section = mysqli_num_rows($query1);
            ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Sections</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $section; ?></div>
                      <div class="mt-2 mb-0 text-muted text-xs">
                        <!-- Placeholder for future data -->
                      </div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-th-list fa-2x text-warning"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- iPads Card -->
            <?php 
            $query1 = mysqli_query($conn, "SELECT * FROM ipad_table");
            $ipad = mysqli_num_rows($query1);
            ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">iPads</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $ipad; ?></div>
                      <div class="mt-2 mb-0 text-muted text-xs">
                        <!-- Placeholder for future data -->
                      </div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-tablet-alt fa-2x text-info"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Scheduling Card -->
            <?php
// Get the current date in 'Y-m-d' format
$currentDate = date('Y-m-d');

// Query to get schedules for today
$query1 = mysqli_query($conn, "SELECT * FROM scheduling_table WHERE DATE(`date`) = '$currentDate'");

// Check for errors in the query
if (!$query1) {
    die("Query failed: " . mysqli_error($conn));
}

// Count the number of schedules
$schedule = mysqli_num_rows($query1);
?>
<div class="col-xl-3 col-md-6 mb-4">
  <div class="card h-100">
    <div class="card-body">
      <div class="row no-gutters align-items-center">
        <div class="col mr-2">
          <div class="text-xs font-weight-bold text-uppercase mb-1">Today's Schedules</div>
          <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $schedule; ?></div>
          <div class="mt-2 mb-0 text-muted text-xs">
            <!-- Placeholder for future data -->
          </div>
        </div>
        <div class="col-auto">
          <i class="fas fa-calendar-check fa-2x text-secondary"></i>
        </div>
      </div>
    </div>
  </div>
</div>


          <!-- Row -->

          <!-- Uncomment if needed
          <div class="row">
            <div class="col-lg-12 text-center">
              <p>Do you like this template? You can download it from 
              <a href="https://github.com/indrijunanda/RuangAdmin" class="btn btn-primary btn-sm" target="_blank">
                <i class="fab fa-fw fa-github"></i>&nbsp;GitHub
              </a>
              </p>
            </div>
          </div>
          -->

        </div>
        <!-- Container Fluid -->
      </div>
      <!-- Footer -->

      <!-- Footer -->
    </div>
  </div>

  <!-- Scroll to top -->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
  <script src="../vendor/chart.js/Chart.min.js"></script>
  <script src="js/demo/chart-area-demo.js"></script>  
</body>

</html>
