<?php
error_reporting(E_ALL);
include '../Includes/dbcon.php';
include '../Includes/session.php';

$userId = $_SESSION['userId'] ?? null;

//------------------------SAVE--------------------------------------------------//
if (isset($_POST['save'])) {
    $course_id = $_POST['course_id'];
    $year_id = $_POST['year_id'];
    $section_id = $_POST['section_id'];
    $lab_class_id = $_POST['lab_class_id'];

    $user_id_query = "SELECT User_ID FROM user_table WHERE Course_ID = ? AND Year_ID = ? AND Section_ID = ?";
    $stmt = $conn->prepare($user_id_query);
    $stmt->bind_param("iii", $course_id, $year_id, $section_id);
    $stmt->execute();
    $user_result = $stmt->get_result(); 

    if ($user_result->num_rows > 0) {
        while ($user_row = $user_result->fetch_assoc()) {
            $user_id = $user_row['User_ID'];

            // Check if enrollment exists
            $checkQuery = "SELECT * FROM enrollment_table WHERE User_ID = ? AND Course_ID = ? AND Year_ID = ? AND Section_ID = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("iiii", $user_id, $course_id, $year_id, $section_id);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                // Update the LabClass_ID for the existing enrollment
                $updateQuery = "UPDATE enrollment_table SET LabClass_ID = ? WHERE User_ID = ? AND Course_ID = ? AND Year_ID = ? AND Section_ID = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param("iiiii", $lab_class_id, $user_id, $course_id, $year_id, $section_id);

                if ($updateStmt->execute()) {
                    
                    $statusMsg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                    Enrollment updated successfully for User ID: $user_id!
                        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                            <span aria-hidden='true'>&times;</span>
                        </button>
                    </div>";
                } else {
                    $statusMsg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    Error updating enrollment for User ID $user_id: " . $updateStmt->error . "
                        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                            <span aria-hidden='true'>&times;</span>
                        </button>
                    </div>";
                }
                $updateStmt->close();
            } else {
                // Insert new enrollment
                $insertQuery = "INSERT INTO enrollment_table (`User_ID`, `Course_ID`, `Year_ID`, `Section_ID`, `LabClass_ID`) 
                                VALUES (?, ?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertQuery);
                $insertStmt->bind_param("iiiii", $user_id, $course_id, $year_id, $section_id, $lab_class_id);

                if ($insertStmt->execute()) {
                    
                    $statusMsg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    No Students found for the selected course, year, laboratory class and section.
                    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                    </button>
                  </div>";
                } else {
                           $statusMsg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    Error creating enrollment for User ID $user_id: " . $insertStmt->error . "
                        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                            <span aria-hidden='true'>&times;</span>
                        </button>
                    </div>";
                }
                $insertStmt->close();
            }
            $checkStmt->close();
        }
    } else {
        $statusMsg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
        No Students found for the selected course, year, laboratory class and section.
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
            <span aria-hidden='true'>&times;</span>
        </button>
      </div>";
    
    }
    $stmt->close();
}

//------------------------DELETE------------------------------------------------//
if (isset($_POST['delete'])) {
    $enrollment_id = $_POST['enrollment_id'];

    if (!empty($enrollment_id)) {
        $deleteQuery = "DELETE FROM enrollment_table WHERE Enrollment_ID = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("i", $enrollment_id);
        if ($stmt->execute()) {
            
            $statusMsg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
            No Students found for the selected course, year, laboratory class and section.
            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                <span aria-hidden='true'>&times;</span>
            </button>
          </div>";
        } else {
            
            $statusMsg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
            Error deleting enrollment: " . $stmt->error . "
                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                    <span aria-hidden='true'>&times;</span>
                </button>
            </div>";
        }
        $stmt->close();
    } else {
       
        $statusMsg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
        Invalid enrollment ID.
            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                <span aria-hidden='true'>&times;</span>
            </button>
        </div>";
    }
}

// Fetch enrollments
    $enrollmentQuery = "SELECT e.*, c.Course_name, y.Year_name, s.Section_name, l.Class_name, u.First_name, u.Last_name
                        FROM enrollment_table e
                        JOIN course_table c ON e.Course_ID = c.Course_ID
                        JOIN year_table y ON e.Year_ID = y.Year_ID
                        JOIN section_table s ON e.Section_ID = s.Section_ID
                        JOIN user_table u ON e.User_ID = u.User_ID
                        JOIN laboratory_class l ON e.LabClass_ID = l.Labclass_ID
                        ORDER BY e.Enrollment_ID DESC"; // Order by Enrollment_ID descending

    $result = mysqli_query($conn, $enrollmentQuery);
    if (!$result) {
        die("Query failed: " . mysqli_error($conn)); // Crucial error check
    }
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
                        <h1 class="h3 mb-0 text-gray-800">Management</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="./">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Enrollment</li>
                        </ol>
                    </div>

                    <?php if (isset($statusMsg)) : ?>
                        <?php echo $statusMsg; ?>
                    <?php endif; ?>
                    

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="m-0 font-weight-bold text-primary">Enrollment Details</h5>
                                </div>
                                <div class="card-body">
                                    <form method="post">
                                        <div class="row">
                                            <!-- First Column (First Row) -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="course_id">Course Name</label>
                                                    <select class="form-control" name="course_id" required>
                                                        <option value="">Select Course</option>
                                                        <?php
                                                        $courseQuery = "SELECT * FROM course_table";
                                                        $courseResult = $conn->query($courseQuery);
                                                        while ($courseRow = $courseResult->fetch_assoc()) {
                                                            echo "<option value='" . $courseRow['Course_ID'] . "'>" . $courseRow['Course_name'] . "</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="year_id">Academic Year</label>
                                                    <select class="form-control" name="year_id" required>
                                                        <option value="">Select Academic Year</option>
                                                        <?php
                                                        $yearQuery = "SELECT * FROM year_table";
                                                        $yearResult = $conn->query($yearQuery);
                                                        while ($yearRow = $yearResult->fetch_assoc()) {
                                                            echo "<option value='" . $yearRow['Year_ID'] . "'>" . $yearRow['Year_name'] . "</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Second Column (First Row) -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="section_id">Class Section</label>
                                                    <select class="form-control" name="section_id" required>
                                                        <option value="">Select Section</option>
                                                        <?php
                                                        $sectionQuery = "SELECT * FROM section_table";
                                                        $sectionResult = $conn->query($sectionQuery);
                                                        while ($sectionRow = $sectionResult->fetch_assoc()) {
                                                            echo "<option value='" . $sectionRow['Section_ID'] . "'>" . $sectionRow['Section_name'] . "</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="lab_class_id">Laboratory Class</label>
                                                    <select class="form-control" name="lab_class_id" required>
                                                        <option value="">Select Laboratory Class</option>
                                                        <?php
                                                        $labQuery = "SELECT * FROM laboratory_class";
                                                        $labResult = $conn->query($labQuery);
                                                        while ($labRow = $labResult->fetch_assoc()) {
                                                            echo "<option value='" . $labRow['Labclass_ID'] . "'>" . $labRow['Class_name'] . "</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Second Row (Submit Button) -->
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group mt-4">
                                                    <button type="submit" name="save" class="btn btn-primary">Save Enrollment</button>
                                                </div>
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
                                        <h5 class="m-0 font-weight-bold text-primary">Enrolled Student</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Student Name</th>
                                                        <th>Course Name</th>
                                                        <th>Academic Year</th>
                                                        <th>Class Section</th>
                                                        <th>Laboratory Class</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    if (mysqli_num_rows($result) > 0) {
                                                        while ($enrollmentRow = mysqli_fetch_assoc($result)) { ?>
                                                            <tr>
                                                                <td><?php echo ucwords(htmlspecialchars($enrollmentRow['First_name'])) . " " . ucwords(htmlspecialchars($enrollmentRow['Last_name'])); ?></td>
                                                                <td><?php echo $enrollmentRow['Course_name']; ?></td>
                                                                <td><?php echo $enrollmentRow['Year_name']; ?></td>
                                                                <td><?php echo $enrollmentRow['Section_name']; ?></td>
                                                                <td><?php echo $enrollmentRow['Class_name']; ?></td>
                                                                <td>
                                                                    <form method="post" style="display:inline;">
                                                                        <input type="hidden" name="enrollment_id" value="<?php echo $enrollmentRow['Enrollment_ID']; ?>">
                                                                        <button type="submit" name="delete" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this enrollment?');">Delete</button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        <?php } 
                                                    } else {
                                                        echo "<tr><td colspan='6'>No students enrolled in laboratory classes for this faculty.</td></tr>";
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                    <!-- End of User Data Display -->

                </div> <!-- Container Fluid -->
            </div>
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

            // Disable or enable dropdowns based on user type
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

        // Ensure state consistency on page load
        document.addEventListener('DOMContentLoaded', toggleDropdowns);
    </script>

</body>

</html>
