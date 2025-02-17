<?php 
error_reporting(E_ALL); // Enable all error reporting for debugging
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Function to create a lab class
function createLabClass($conn) {
    $user_id = $_POST['user_id'];
    $class_name = $_POST['class_name'];
    $course_id = $_POST['course_id'];
    $year_id = $_POST['year_id'];
    $section_id = $_POST['section_id'];  // Get Section_ID from the form
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

    // Insert lab class into the database, including Section_ID
    $query = "INSERT INTO laboratory_class (User_ID, Class_name, Course_ID, Year_ID, Section_ID, Start_Time, End_Time, Date) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isssssss", $user_id, $class_name, $course_id, $year_id, $section_id, $start_time, $end_time, $date);
    
    return $stmt->execute();
}


// Function to edit lab class data
function editLabClass($conn, $labClassId) {
    if ($stmt = $conn->prepare("SELECT lc.*, y.Year_Name, c.Course_Name, s.Section_ID, s.Section_Name
                                FROM laboratory_class lc
                                JOIN year_table y ON lc.Year_ID = y.Year_ID
                                JOIN course_table c ON lc.Course_ID = c.Course_ID
                                JOIN section_table s ON lc.Section_ID = s.Section_ID
                                WHERE lc.Labclass_ID = ?")) {
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
    $section_id = $_POST['section_id']; // Get Section_ID from POST data
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $date = $_POST['date'];

    // Prepare the SQL query to update the lab class details
    $query = "UPDATE laboratory_class 
              SET User_ID = ?, Class_name = ?, Course_ID = ?, Year_ID = ?, Section_ID = ?, Start_Time = ?, End_Time = ?, Date = ? 
              WHERE Labclass_ID = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isssssssi", $user_id, $class_name, $course_id, $year_id, $section_id, $start_time, $end_time, $date, $labClassId);
    
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
            $statusMsg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
            Lab Class Created Successfully!
            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                <span aria-hidden='true'>&times;</span>
            </button>
        </div>";
        } else {
         
            $statusMsg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
            Error: Invalid Faculty User ID.
            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                <span aria-hidden='true'>&times;</span>
            </button>
        </div>";
        }
    } elseif (isset($_POST['edit'])) {
        $labClassId = $_POST['labClassId'];
        if (updateLabClass($conn, $labClassId)) {
            $statusMsg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
            Lab Class Updated Successfully!
            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                <span aria-hidden='true'>&times;</span>
            </button>
        </div>";
        } else {
           
            $statusMsg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
            Error updating lab class.
            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                <span aria-hidden='true'>&times;</span>
            </button>
        </div>";
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['Labclass_ID'])) {
    $labClassId = $_GET['Labclass_ID'];
    if (deleteLabClass($conn, $labClassId)) {
        
        $statusMsg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
        Lab Class Deleted Successfully!
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
            <span aria-hidden='true'>&times;</span>
        </button>
    </div>";
    } else {
       
        $statusMsg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
        Error deleting lab class. Please ensure there are no dependencies.
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
            <span aria-hidden='true'>&times;</span>
        </button>
    </div>";
    }
}

$labClassData = [];
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['Labclass_ID'])) {
    $labClassId = $_GET['Labclass_ID'];
    $labClassData = editLabClass($conn, $labClassId);
}

// Fetch all lab classes with YEAR_ID and COURSE_NAME for display
$labClassQuery = "SELECT lc.*, y.Year_Name, c.Course_Name, u.First_name, u.Last_name, u.User_ID, u.School_ID, s.Section_ID, s.Section_Name 
                  FROM laboratory_class lc
                  JOIN year_table y ON lc.Year_ID = y.Year_ID
                  JOIN course_table c ON lc.Course_ID = c.Course_ID
                  JOIN user_table u ON lc.User_ID = u.User_ID
                  JOIN section_table s ON lc.Section_ID = s.Section_ID
                  ORDER BY lc.Labclass_ID DESC";
$labClassResult = mysqli_query($conn, $labClassQuery);




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="img/logo/qc.png" rel="icon">
  <title>Manage Users</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="css/ruang-admin.min.css" rel="stylesheet">
</head>
                <script>
                    $(document).ready(function () {
                    $('#dataTable').DataTable(); // ID From dataTable 
                    $('#dataTableHover').DataTable(); // ID From dataTable with Hover
                    });
                </script>


<style>
    .table {
    font-size: 0.875rem; /* Adjusts the font size to a smaller size */
}

.table th, .table td {
    padding: 0.5rem; /* Adjusts padding for a tighter fit */
}
</style>

<!-- <script>
    $(document).ready(function () {
      $('#dataTable').DataTable(); // ID From dataTable 
      $('#dataTableHover').DataTable(); // ID From dataTable with Hover
    });
  </script> -->
<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <?php include "Includes/sidebar.php"; ?>
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- TopBar -->
                <?php include "Includes/topbar.php"; ?>
                
                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Laboratory Schedule</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="./">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Schedule Form</li>
                        </ol>
                    </div>
                    <?php if (isset($statusMsg)) : ?>
                                            <?php echo $statusMsg; ?>
                                        <?php endif; ?>
                    
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="m-0 font-weight-bold text-primary">Laboratory Schedule Form</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="post">
                                            <input type="hidden" name="labClassId" value="<?php echo $labClassData['Labclass_ID'] ?? ''; ?>">

                                            <div class="row">
                                                <!-- First Column -->
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="userId">Instructor ID (Teaching Staff)</label>
                                                        <select class="form-control" id="userId" name="user_id" required>
                                                            <option value="">Select Instructor by ID</option>
                                                            <?php while ($user = mysqli_fetch_assoc($userResult)): ?>
                                                                <option value="<?php echo $user['User_ID']; ?>" <?php echo (isset($labClassData['User_ID']) && $labClassData['User_ID'] == $user['User_ID']) ? 'selected' : ''; ?>>
                                                                    <?php echo $user['User_ID']; ?>
                                                                </option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="className">Class Name</label>
                                                        <input type="text" class="form-control" id="className" name="class_name" required value="<?php echo $labClassData['Class_name'] ?? ''; ?>">
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="courseId">Course Name</label>
                                                        <select class="form-control" id="courseId" name="course_id" required>
                                                            <option value="">Select Course</option>
                                                            <?php while ($course = mysqli_fetch_assoc($courseResult)): ?>
                                                                <option value="<?php echo $course['Course_ID']; ?>" <?php echo (isset($labClassData['Course_ID']) && $labClassData['Course_ID'] == $course['Course_ID']) ? 'selected' : ''; ?>>
                                                                    <?php echo $course['Course_Name']; ?>
                                                                </option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <!-- Second Column -->
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="yearId">Academic Year</label>
                                                        <select class="form-control" id="yearId" name="year_id" required>
                                                            <option value="">Select Academic Year</option>
                                                            <?php while ($year = mysqli_fetch_assoc($yearResult)): ?>
                                                                <option value="<?php echo $year['Year_ID']; ?>" <?php echo (isset($labClassData['Year_ID']) && $labClassData['Year_ID'] == $year['Year_ID']) ? 'selected' : ''; ?>>
                                                                    <?php echo $year['Year_Name']; ?>
                                                                </option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="sectionId">Section</label>
                                                        <select class="form-control" id="sectionId" name="section_id" required>
                                                            <option value="">Select Section</option>
                                                            <?php
                                                            $sectionQuery = "SELECT Section_ID, Section_Name FROM section_table";
                                                            $sectionResult = mysqli_query($conn, $sectionQuery);
                                                            while ($section = mysqli_fetch_assoc($sectionResult)) {
                                                                $selected = (isset($labClassData['Section_ID']) && $labClassData['Section_ID'] == $section['Section_ID']) ? 'selected' : '';
                                                                echo "<option value='" . $section['Section_ID'] . "' $selected>" . $section['Section_Name'] . "</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="date">Class Date</label>
                                                        <input type="date" class="form-control" id="date" name="date" required value="<?php echo $labClassData['Date'] ?? ''; ?>" 
                                                            min="<?php echo date('Y-m-d'); ?>">
                                                    </div>
                                                </div>

                                                <!-- Third Column -->
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="startTime">Start Time</label>
                                                        <input type="time" class="form-control" id="startTime" name="start_time" 
                                                            required min="08:00" max="20:00" 
                                                            value="<?php echo $labClassData['Start_Time'] ?? ''; ?>" 
                                                            oninput="validateTimes()">
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="endTime">End Time</label>
                                                        <input type="time" class="form-control" id="endTime" name="end_time" 
                                                            required min="08:00" max="20:00" 
                                                            value="<?php echo $labClassData['End_Time'] ?? ''; ?>" 
                                                            oninput="validateTimes()">
                                                    </div>

                                                    <script>
                                                    function validateTimes() {
                                                        const startTimeInput = document.getElementById('startTime');
                                                        const endTimeInput = document.getElementById('endTime');

                                                        const startTime = startTimeInput.value;
                                                        const endTime = endTimeInput.value;

                                                        if (startTime && endTime) {
                                                            const startDateTime = new Date('1970-01-01T' + startTime + 'Z');
                                                            const endDateTime = new Date('1970-01-01T' + endTime + 'Z');

                                                            const timeDifference = (endDateTime - startDateTime) / 1000 / 60 / 60;

                                                            if (endTime <= startTime) {
                                                                endTimeInput.setCustomValidity('End Time must be later than Start Time.');
                                                            } else if (timeDifference < 1) {
                                                                endTimeInput.setCustomValidity('The class duration must be at least 1 hour.');
                                                            } else if (timeDifference > 5) {
                                                                endTimeInput.setCustomValidity('The class duration cannot exceed 5 hours.');
                                                            } else {
                                                                endTimeInput.setCustomValidity('');
                                                            }
                                                        }
                                                    }
                                                    </script>

                                                    <button type="submit" name="submit" class="btn btn-primary btn-block">Create Class</button>
                                                    <button type="submit" name="edit" class="btn btn-warning btn-block">Update Class</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>


                                        <!-- Display User Data -->
                        <div class="row mt-4">
                            <div class="col-lg-12">
                                <div class="card mb-4">
                                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                        <h6 class="m-0 font-weight-bold text-primary">Laboratory Schedule</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                        <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                            <thead class="thead-light">
                            <tr>
                                    <th>User ID</th> <!-- Added User ID column -->
                                    <th>Instructor Name</th>
                                    <th>Course Name</th>
                                    <th>Class Subject</th>
                                    <th>Section Name</th>
                                    <th>Academic Year</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Class Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($labClass = mysqli_fetch_assoc($labClassResult)): ?>
                                    <tr>
                                        <!-- Display User ID -->
                                        <td><?php echo $labClass['School_ID']; ?></td>
                                        
                                        <!-- Capitalize the first letter of both first and last name -->
                                        <td><?php echo ucwords(strtolower($labClass['First_name'])) . " " . ucwords(strtolower($labClass['Last_name'])); ?></td>
                                        <td><?php echo $labClass['Course_Name']; ?></td>
                                        <td><?php echo $labClass['Class_name']; ?></td>
                                        <td><?php echo $labClass['Section_Name']; ?></td>
                                        <td><?php echo $labClass['Year_Name']; ?></td>
                                        <td><?php echo date("g:i A", strtotime($labClass['Start_Time'])); ?></td>
                                        <td><?php echo date("g:i A", strtotime($labClass['End_Time'])); ?></td>
                                        <td>
                                            <?php
                                            $date = strtotime($labClass['Date']);
                                            if ($date !== false) {
                                                echo date("F j, Y", $date); // Or any other desired format
                                            } else {
                                                echo "Invalid Date";
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <a href="?action=edit&Labclass_ID=<?php echo $labClass['Labclass_ID']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <a href="?action=delete&Labclass_ID=<?php echo $labClass['Labclass_ID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this lab class?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
                <!-- Container Fluid -->
</div>
            <!-- Footer -->
      

    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

  
    <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
   <!-- Page level plugins -->
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

  <!-- Page level custom scripts -->
 <script>
  $(document).ready(function () {
    $('#dataTable').DataTable({
      "order": [[0, 'desc']] // Sort by the first column (index 0) in descending order
    });
    
    $('#dataTableHover').DataTable({
      "order": [[0, 'desc']] // Sort by the first column (index 0) in descending order with hover effect
    });
  });
</script>


<script>
function toggleDropdowns() {
    const userType = document.getElementById('user_type').value;
    const courseDropdown = document.getElementById('course_id');
    const yearDropdown = document.getElementById('year_id');
    const sectionDropdown = document.getElementById('section_id');

    // Determine if dropdowns should be disabled or enabled
    const disableDropdowns = userType == '1' || userType == '2'; // Assume 1 = Admin, 2 = Faculty
    const enableDropdowns = userType == '3'; // Assume 3 = Student

    if (disableDropdowns) {
        courseDropdown.setAttribute('disabled', 'disabled');
        yearDropdown.setAttribute('disabled', 'disabled');
        sectionDropdown.setAttribute('disabled', 'disabled');
    } else if (enableDropdowns) {
        courseDropdown.removeAttribute('disabled');
        yearDropdown.removeAttribute('disabled');
        sectionDropdown.removeAttribute('disabled');
    } else {
        // Reset to default behavior
        courseDropdown.setAttribute('disabled', 'disabled');
        yearDropdown.setAttribute('disabled', 'disabled');
        sectionDropdown.setAttribute('disabled', 'disabled');
    }
}

// Run the toggle function on page load to ensure state consistency
document.addEventListener('DOMContentLoaded', toggleDropdowns);
</script>
</body>
</html>
