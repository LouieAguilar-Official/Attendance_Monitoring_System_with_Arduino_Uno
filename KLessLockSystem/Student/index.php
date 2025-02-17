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

// Fetch the user's enrollment data
$enrollmentQuery = "
    SELECT 
        CONCAT(user_table.First_name, ' ', user_table.Last_name) AS Instructor,
        laboratory_class.Class_name,
        laboratory_class.Start_Time,
        laboratory_class.End_Time,
        laboratory_class.Date,
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
    JOIN user_table ON laboratory_class.User_ID = user_table.User_ID
    WHERE enrollment_table.User_ID = ?
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

// Count the number of enrollment records
$classCount = count($enrollmentRecords);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <link href="img/logo/kl.png" rel="icon">
  <title>Dashboard</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
  
  <!-- FullCalendar -->
  <link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css' rel='stylesheet' />
  <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>
  <script src='https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js'></script>
  <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js'></script>
  
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
            <h1 class="h3 mb-0 text-gray-800">Student Dashboard</h1>
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
            <!-- <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Users</div>
                      <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><?php echo $user; ?></div>
                      <div class="mt-2 mb-0 text-muted text-xs"> -->
                        <!-- Placeholder for future data -->
                      <!-- </div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-users fa-2x text-info"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div> -->

            <!-- Administrator Card -->
            <?php 
            $query1 = mysqli_query($conn, "SELECT * FROM user_table WHERE Usertype_ID = 1");
            $admin = mysqli_num_rows($query1);
            ?>
            <!-- <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Administrator</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $admin; ?></div>
                      <div class="mt-2 mb-0 text-muted text-xs"> -->
                        <!-- Placeholder for future data -->
                      <!-- </div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-user-shield fa-2x text-primary"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div> -->

            <!-- Faculty Card -->
            <?php 
            $query1 = mysqli_query($conn, "SELECT * FROM user_table WHERE Usertype_ID = 2");
            $faculty = mysqli_num_rows($query1);
            ?>
            <!-- <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Faculty</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $faculty; ?></div>
                      <div class="mt-2 mb-0 text-muted text-xs"> -->
                        <!-- Placeholder for future data -->
                      <!-- </div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-chalkboard-teacher fa-2x text-success"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div> -->

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
          
<!-- Display the number of classes -->
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card h-100">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-uppercase mb-1">Classes</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $classCount; ?></div>
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
            <!-- <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Sections</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $section; ?></div>
                      <div class="mt-2 mb-0 text-muted text-xs"> -->
                        <!-- Placeholder for future data -->
                      <!-- </div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-th-list fa-2x text-warning"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div> -->

            <!-- iPads Card -->
            <?php 
            $query1 = mysqli_query($conn, "SELECT * FROM ipad_table");
            $ipad = mysqli_num_rows($query1);
            ?>
            <!-- <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">iPads</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $ipad; ?></div>
                      <div class="mt-2 mb-0 text-muted text-xs"> -->
                        <!-- Placeholder for future data -->
                      <!-- </div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-tablet-alt fa-2x text-info"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div> -->

            <!-- Scheduling Card -->
            <?php 
            $query1 = mysqli_query($conn, "SELECT * FROM scheduling_table");
            $schedule = mysqli_num_rows($query1);
            ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Schedules</div>
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
  <!-- <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a> -->

  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
  <script src="../vendor/chart.js/Chart.min.js"></script>
  <script src="js/demo/chart-area-demo.js"></script>  

  <!-- <script>
    $(document).ready(function() {
      var events = <?php echo json_encode($events); ?>; // Pass PHP array to JavaScript

      $('#calendar').fullCalendar({
        header: {
          left: 'prev,next today',
          center: 'title',
          right: 'month,agendaWeek,agendaDay'
        },
        editable: true,
        events: events // Use fetched events
      });
    });
  </script> -->
</body>

</html>

