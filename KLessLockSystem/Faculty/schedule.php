<?php 
error_reporting(E_ALL); // Enable all error reporting for debugging
include '../Includes/dbcon.php';
include '../Includes/session.php';
$userId = $_SESSION['userId'] ?? null;

// Function to create a lab class
function createLabClass($conn) {
    $user_id = $_POST['user_id'];
    $class_name = $_POST['class_name'];
    $course_id = $_POST['course_id'];
    $year_id = $_POST['year_id']; 
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $date = $_POST['date'];

    // Check if the user is a Faculty (Usertype_ID = 2)
    $userCheckQuery = "SELECT * FROM user_table WHERE User_ID = ? AND Usertype_ID = 2";
    $stmt = $conn->prepare($userCheckQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return false; // User is not a Faculty
    }

    // Insert lab class into the database
    $query = "INSERT INTO laboratory_class (User_ID, Class_name, Course_ID, Year_ID, Start_Time, End_Time, Date) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("issssss", $user_id, $class_name, $course_id, $year_id, $start_time, $end_time, $date);
    
    return $stmt->execute();
}

// Function to edit lab class data
function editLabClass($conn, $labClassId) {
    if ($stmt = $conn->prepare("SELECT * FROM laboratory_class WHERE Labclass_ID=?")) {
        $stmt->bind_param("i", $labClassId);
        $stmt->execute();
        $result = $stmt->get_result();
        $labClassData = $result->fetch_assoc();
        $stmt->close();
        return $labClassData;
    }
    return null;
}

// Function to update a lab class
function updateLabClass($conn, $labClassId) {
    $user_id = $_POST['user_id'];
    $class_name = $_POST['class_name'];
    $course_id = $_POST['course_id'];
    $year_id = $_POST['year_id'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $date = $_POST['date'];

    // Prepare the SQL query to update the lab class details
    $query = "UPDATE laboratory_class 
              SET User_ID = ?, Class_name = ?, Course_ID = ?, Year_ID = ?, Start_Time = ?, End_Time = ?, Date = ? 
              WHERE Labclass_ID = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("issssssi", $user_id, $class_name, $course_id, $year_id, $start_time, $end_time, $date, $labClassId);
    
    return $stmt->execute();
}

// Function to delete attendance records before deleting a lab class
function deleteAttendanceRecords($conn, $labClassId) {
    $query = "DELETE FROM attendance_table WHERE Labclass_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $labClassId);
    return $stmt->execute(); // Return true on success, false otherwise
}

// Function to delete a lab class
function deleteLabClass($conn, $labClassId) {
    // First, delete related attendance records
    if (!deleteAttendanceRecords($conn, $labClassId)) {
        return false; // Failed to delete attendance records
    }

    // Now delete the lab class
    $query = "DELETE FROM laboratory_class WHERE Labclass_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $labClassId);
    return $stmt->execute(); // Return true on success, false otherwise
}

// Fetch all users with Usertype_ID = 2
$userQuery = "SELECT User_ID FROM user_table WHERE Usertype_ID = 2";
$userResult = mysqli_query($conn, $userQuery);

// Fetch all courses
$courseQuery = "SELECT Course_ID, Course_Name FROM course_table"; 
$courseResult = mysqli_query($conn, $courseQuery);

// Fetch all years from year_table
$yearQuery = "SELECT Year_ID, Year_Name FROM year_table"; 
$yearResult = mysqli_query($conn, $yearQuery);

// Handle save, delete, and edit actions
$statusMsg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit'])) {
        if (createLabClass($conn)) {
            $statusMsg = "<div class='alert alert-success'>Lab Class Created Successfully!</div>";
        } else {
            $statusMsg = "<div class='alert alert-danger'>Error: Invalid Faculty User ID.</div>";
        }
    } elseif (isset($_POST['edit'])) {
        $labClassId = $_POST['labClassId'];
        if (updateLabClass($conn, $labClassId)) {
            $statusMsg = "<div class='alert alert-success'>Lab Class Updated Successfully!</div>";
        } else {
            $statusMsg = "<div class='alert alert-danger'>Error updating lab class.</div>";
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['Labclass_ID'])) {
    $labClassId = $_GET['Labclass_ID'];
    if (deleteLabClass($conn, $labClassId)) {
        $statusMsg = "<div class='alert alert-success'>Lab Class Deleted Successfully!</div>";
    } else {
        $statusMsg = "<div class='alert alert-danger'>Error deleting lab class. Please ensure there are no dependencies.</div>";
    }
}

$labClassData = [];
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['Labclass_ID'])) {
    $labClassId = $_GET['Labclass_ID'];
    $labClassData = editLabClass($conn, $labClassId);
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
            <h1 class="h3 mb-0 text-gray-800"> Laboratory Schedule</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active">All Laboratory</li>
            </ol>
          </div>

          <!-- Scheduling Table -->
          <div class="col-lg-12">
            <!-- First Table: Scheduling Entries -->
                    <div class="card mb-4">
                                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                        <h5 class="m-0 font-weight-bold text-primary">Laboratory Schedule</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                                                <thead class="thead-light">
                                                <tr>
                                                    <th>Instructor Name</th>
                                                    <th>Class Title</th>
                                                    <th>Subject Name</th>
                                                    <th>Academic Year</th>
                                                    <th>Session Start Time</th>
                                                    <th>Session End Time</th>
                                                    <th>Scheduled Date</th>
                                                    
                                                </tr>
                                            </thead>
                                            <tbody>
                                                    <?php
                                                    $labClassQuery = "
                                                        SELECT lc.*, y.Year_Name, c.Course_Name, u.First_name, u.Last_name
                                                        FROM laboratory_class lc
                                                        JOIN year_table y ON lc.Year_ID = y.Year_ID
                                                        JOIN course_table c ON lc.Course_ID = c.Course_ID
                                                        JOIN user_table u ON lc.User_ID = u.User_ID
                                                        WHERE lc.User_ID = ?";

                                                    $stmt = mysqli_prepare($conn, $labClassQuery);
                                                    if ($stmt) {
                                                        mysqli_stmt_bind_param($stmt, "s", $userId);
                                                        mysqli_stmt_execute($stmt);
                                                        $labClassResult = mysqli_stmt_get_result($stmt);

                                                        if ($labClassResult) {
                                                            if (mysqli_num_rows($labClassResult) > 0) {
                                                                while ($labClass = mysqli_fetch_assoc($labClassResult)): ?>
                                                                    <tr>
                                                                    <td>
                                                                        <?php 
                                                                        $firstName = ucfirst(strtolower($labClass['First_name'])); // Capitalize the first letter of the first name
                                                                        $lastName = ucfirst(strtolower($labClass['Last_name']));   // Capitalize the first letter of the last name
                                                                        echo htmlspecialchars($firstName) . " " . htmlspecialchars($lastName); 
                                                                        ?>
                                                                    </td>

                                                                        <td><?php echo htmlspecialchars($labClass['Class_name']); ?></td>
                                                                        <td><?php echo htmlspecialchars($labClass['Course_Name']); ?></td>
                                                                        <td><?php echo htmlspecialchars($labClass['Year_Name']); ?></td>
                                                                        <td>
                                                                            <?php
                                                                            $startTime = strtotime($labClass['Start_Time']);
                                                                            echo ($startTime !== false) ? date("g:i A", $startTime) : "Invalid Time";
                                                                            ?>
                                                                        </td>
                                                                        <td>
                                                                            <?php
                                                                            $endTime = strtotime($labClass['End_Time']);
                                                                            echo ($endTime !== false) ? date("g:i A", $endTime) : "Invalid Time";
                                                                            ?>
                                                                        </td>
                                                                        <td>
                                                                            <?php
                                                                            $date = strtotime($labClass['Date']);
                                                                            echo ($date !== false) ? date("F j, Y", $date) : "Invalid Date";
                                                                            ?>
                                                                        </td>
                                                                       
                                                                    </tr>
                                                                <?php endwhile;
                                                            } else {
                                                                echo "<tr><td colspan='8'>No laboratory classes scheduled for this faculty.</td></tr>";
                                                            }
                                                        } else {
                                                            die("Error getting result: " . mysqli_error($conn));
                                                        }
                                                        mysqli_stmt_close($stmt);
                                                    } else {
                                                        die("Error preparing query: " . mysqli_error($conn));
                                                    }
                                                    ?>
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
