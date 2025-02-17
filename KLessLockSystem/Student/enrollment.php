<?php 
error_reporting(E_ALL);
include '../Includes/dbcon.php';
include '../Includes/session.php';

//------------------------SAVE--------------------------------------------------//
if (isset($_POST['save'])) {
    $course_id = $_POST['course_id'];
    $year_id = $_POST['year_id'];
    $section_id = $_POST['section_id'];
    $lab_class_id = $_POST['lab_class_id'];

    // Fetch User_ID for Faculty based on the selected Course_ID, Year_ID, and Section_ID
    $user_id_query = "SELECT User_ID FROM user_table WHERE Course_ID = '$course_id' LIMIT 1";
    $user_result = mysqli_query($conn, $user_id_query);
    $user_row = mysqli_fetch_assoc($user_result);

    // Check if User_ID exists
    if ($user_row) {
        $user_id = $user_row['User_ID'];

        // Construct the SQL query
        $query = "INSERT INTO enrollment_table (`User_ID`, `Course_ID`, `Year_ID`, `Section_ID`, `LabClass_ID`) 
                  VALUES ('$user_id', '$course_id', '$year_id', '$section_id', '$lab_class_id')";

        // Execute the query
        if (mysqli_query($conn, $query)) {
            $statusMsg = "<div class='alert alert-success'>Enrollment Created Successfully!</div>";
        } else {
            $statusMsg = "<div class='alert alert-danger'>An error occurred: " . mysqli_error($conn) . "</div>";
        }
    } else {
        $statusMsg = "<div class='alert alert-danger'>User not found for the selected course.</div>";
    }
}

//------------------------DELETE---------------------------------------------------//
if (isset($_POST['delete'])) {
    // Check if required POST parameter is set
    if (isset($_POST['enrollment_id'])) {
        $enrollment_id = $_POST['enrollment_id'];

        // Construct the delete query
        $deleteQuery = "DELETE FROM enrollment_table WHERE Enrollment_ID = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("i", $enrollment_id);
        
        if ($stmt->execute()) {
            $statusMsg = "<div class='alert alert-success'>Enrollment deleted successfully!</div>";
        } else {
            $statusMsg = "<div class='alert alert-danger'>Error deleting enrollment: " . $stmt->error . "</div>";
        }
        $stmt->close();
    } else {
        $statusMsg = "<div class='alert alert-danger'>Enrollment ID is required for deletion.</div>";
    }
}

// Fetch all enrollments including Faculty User_ID and LabClass_name
$enrollmentQuery = "SELECT e.*, c.Course_name, y.Year_name, s.Section_name, l.Class_name, u.User_ID 
                    FROM enrollment_table e 
                    JOIN course_table c ON e.Course_ID = c.Course_ID 
                    JOIN year_table y ON e.Year_ID = y.Year_ID 
                    JOIN section_table s ON e.Section_ID = s.Section_ID 
                    JOIN user_table u ON e.User_ID = u.User_ID 
                    JOIN laboratory_class l ON e.LabClass_ID = l.Labclass_ID"; 

// Execute the query
$result = mysqli_query($conn, $enrollmentQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="img/logo/attnlg.jpg" rel="icon">
    <title>Enrollment Page</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="css/ruang-admin.min.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include "Includes/sidebar.php"; ?>
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include "Includes/topbar.php"; ?>
                
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Manage Enrollment</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="./">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Manage Enrollment</li>
                        </ol>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between">
                                    <h5 class="m-0 font-weight-bold text-primary">Enrollment Details</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (isset($statusMsg)) : ?>
                                        <?php echo $statusMsg; ?>
                                    <?php endif; ?>

                                    <form method="post">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="course_id">Course ID</label>
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
                                                    <label for="year_id">Year ID</label>
                                                    <select class="form-control" name="year_id" required>
                                                        <option value="">Select Year</option>
                                                        <?php
                                                        $yearQuery = "SELECT * FROM year_table";
                                                        $yearResult = $conn->query($yearQuery);
                                                        while ($yearRow = $yearResult->fetch_assoc()) {
                                                            echo "<option value='" . $yearRow['Year_ID'] . "'>" . $yearRow['Year_name'] . "</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="section_id">Section ID</label>
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
                                                    <label for="lab_class_id">Lab Class</label>
                                                    <select class="form-control" name="lab_class_id" required>
                                                        <option value="">Select Lab Class</option>
                                                        <?php
                                                        $labClassQuery = "SELECT * FROM laboratory_class";
                                                        $labClassResult = $conn->query($labClassQuery);
                                                        while ($labClassRow = $labClassResult->fetch_assoc()) {
                                                            echo "<option value='" . $labClassRow['Labclass_ID'] . "'>" . $labClassRow['Class_name'] . "</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" name="save" class="btn btn-primary btn-lg btn-block">Submit</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Display Enrollment Data -->
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Enrollment List</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>User ID</th>
                                                    <th>Course</th>
                                                    <th>Year</th>
                                                    <th>Section</th>
                                                    <th>Lab Class</th>
                                                    <th>Action</th> <!-- Action Column -->
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                                while ($enrollmentRow = $result->fetch_assoc()) {
                                                    echo "<tr>
                                                    <td>{$enrollmentRow['User_ID']}</td>
                                                    <td>{$enrollmentRow['Course_name']}</td>
                                                    <td>{$enrollmentRow['Year_name']}</td>
                                                    <td>{$enrollmentRow['Section_name']}</td>
                                                    <td>{$enrollmentRow['Class_name']}</td>
                                                    <td>
                                                        <form method='post'>
                                                            <input type='hidden' name='enrollment_id' value='{$enrollmentRow['Enrollment_ID']}'>
                                                            <button type='submit' name='delete' class='btn btn-danger'>Delete</button>
                                                        </form>
                                                    </td>
                                                    </tr>";
                                                }
                                            ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include "Includes/footer.php"; ?>
        </div>
    </div>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/ruang-admin.min.js"></script>
</body>
</html>
