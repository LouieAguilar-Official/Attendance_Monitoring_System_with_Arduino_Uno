<?php 
error_reporting(E_ALL); // Enable all error reporting for debugging
$host = "localhost";
	$user = "root";
	$pass = "";
	$db = "kless_lock_db";
	
	$conn = new mysqli($host, $user, $pass, $db);
	if($conn->connect_error){
		echo "Seems like you have not configured the database. Failed To Connect to database:" . $conn->connect_error;
	}

//------------------------SAVE--------------------------------------------------
if (isset($_POST['save'])) {
    $school_id = $_POST['school_id'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $gender = $_POST['gender'];
    $birthday = $_POST['birthday'];
    $address = $_POST['address'];
    $contact_num = $_POST['contact_num'];
    $user_type = $_POST['user_type'];
    $email = $_POST['email'];
    $sampPass = $_POST['password'];
    $password = md5($sampPass);
    // $pin = $_POST['pin'];

    // Set course_id, year_id, section_id to NULL for user types 1 and 2
    $course_id = ($user_type == 1 || $user_type == 2) ? "NULL" : $_POST['course_id'];
    $year_id = ($user_type == 1 || $user_type == 2) ? "NULL" : $_POST['year_id'];
    $section_id = ($user_type == 1 || $user_type == 2) ? "NULL" : $_POST['section_id'];

    // Construct the SQL query
    $query = "INSERT INTO user_table (`School_ID`, `First_name`, `Middle_name`, `Last_name`, `Gender`, 
              `Birthday`, `Address`, `Contact_num`, `Usertype_ID`, `Course_ID`, `Year_ID`, 
              `Section_ID`, `User_email`, `password`) 
              VALUES ('$school_id', '$first_name', '$middle_name', '$last_name', '$gender', 
              '$birthday', '$address', '$contact_num', '$user_type', $course_id, 
              $year_id, $section_id, '$email', '$password')";

    // Execute the query
    if (mysqli_query($conn, $query)) {
        $statusMsg = "<div class='alert alert-success'>Created Successfully!</div>";
    } else {
        $statusMsg = "<div class='alert alert-danger'>An error occurred: " . mysqli_error($conn) . "</div>";
    }
}


if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['User_ID'])) {
    $userId = $_GET['User_ID'];

    // Begin transaction
    mysqli_begin_transaction($conn);

    // Array to hold statement variables for closing later
    $statements = [];

    try {
        // Step 1: Delete from record_table (if it exists)
        $deleteRecordQuery = "DELETE FROM record_table WHERE Fingerprint_ID IN (SELECT Fingerprint_ID FROM fingerprint_table WHERE User_ID = ?)";
        $stmt1 = $conn->prepare($deleteRecordQuery);
        $stmt1->bind_param("i", $userId);
        $stmt1->execute();
        $statements[] = $stmt1;

        // Step 2: Delete from entry_table
        $deleteEntryQuery = "DELETE FROM entry_table WHERE User_ID = ?";
        $stmt2 = $conn->prepare($deleteEntryQuery);
        $stmt2->bind_param("i", $userId);
        $stmt2->execute();
        $statements[] = $stmt2;

        // Step 3: Delete from fingerprint_table
        $deleteFingerprintQuery = "DELETE FROM fingerprint_table WHERE User_ID = ?";
        $stmt3 = $conn->prepare($deleteFingerprintQuery);
        $stmt3->bind_param("i", $userId);
        $stmt3->execute();
        $statements[] = $stmt3;

        // Step 4: Delete from laboratory_class
        $deleteLabQuery = "DELETE FROM laboratory_class WHERE User_ID = ?";
        $stmt4 = $conn->prepare($deleteLabQuery);
        $stmt4->bind_param("i", $userId);
        $stmt4->execute();
        $statements[] = $stmt4;

        // Step 5: Delete from borrow
        $deleteBorrowQuery = "DELETE FROM borrow WHERE User_ID = ?";
        $stmt5 = $conn->prepare($deleteBorrowQuery);
        $stmt5->bind_param("i", $userId);
        $stmt5->execute();
        $statements[] = $stmt5;

        // Step 6: Delete from scheduling_table
        $deleteScheduleQuery = "DELETE FROM scheduling_table WHERE User_ID = ?";
        $stmt6 = $conn->prepare($deleteScheduleQuery);
        $stmt6->bind_param("i", $userId);
        $stmt6->execute();
        $statements[] = $stmt6;

        // Step 7: Delete from userschedule_table
        $deleteUserScheduleQuery = "DELETE FROM userschedule_table WHERE User_ID = ?";
        $stmt7 = $conn->prepare($deleteUserScheduleQuery);
        $stmt7->bind_param("i", $userId);
        $stmt7->execute();
        $statements[] = $stmt7;

        // Step 8: Delete from attendance_table
        $deleteAttendanceQuery = "DELETE FROM attendance_table WHERE User_ID = ?";
        $stmt8 = $conn->prepare($deleteAttendanceQuery);
        $stmt8->bind_param("i", $userId);
        $stmt8->execute();
        $statements[] = $stmt8;

        // Step 9: Finally, delete the user from the user_table
        $deleteUserQuery = "DELETE FROM user_table WHERE User_ID = ?";
        $stmt9 = $conn->prepare($deleteUserQuery);
        $stmt9->bind_param("i", $userId);
        $stmt9->execute();
        $statements[] = $stmt9;

        // Commit the transaction
        mysqli_commit($conn);
        $statusMsg = "<div class='alert alert-success'>User and related records deleted successfully!</div>";

    } catch (Exception $e) {
        // Rollback the transaction if something failed
        mysqli_rollback($conn);
        $statusMsg = "<div class='alert alert-danger'>Error deleting user: " . $e->getMessage() . "</div>";
    }

    // Close all prepared statements
    foreach ($statements as $stmt) {
        if ($stmt) {
            $stmt->close();
        }
    }
}

//------------------------EDIT---------------------------------------------------
$userData = []; // Initialize array for user data
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['User_ID'])) {
    $Id = $_GET['User_ID'];

    // Prepare statement to fetch user data
    if ($stmt = $conn->prepare("SELECT * FROM user_table WHERE User_ID=?")) {
        $stmt->bind_param("i", $Id);
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc(); // Store user data in an array
        $stmt->close();
    }

    // Handle form submission for updating user data
    if (isset($_POST['edit'])) {
        // Your existing edit logic...
    }
}




// Fetch all users
$userQuery = "SELECT * FROM user_table";
$result = mysqli_query($conn, $userQuery);




?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link href="img/logo/attnlg.jpg" rel="icon">
    <title>klesslock - Registration</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
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
     
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- TopBar -->
            
                
                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">KlessLock Registration Form</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="./">Login Page</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Registration Form</li>
                        </ol>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="m-0 font-weight-bold text-primary">User Details</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (isset($statusMsg)) : ?>
                                        <?php echo $statusMsg; ?>
                                    <?php endif; ?>

                                    
 <form method="post">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="school_id">School ID</label>
                <input type="text" class="form-control" name="school_id" value="<?php echo isset($userData['School_ID']) ? $userData['School_ID'] : ''; ?>" required>
            </div>
            <div class="form-group">
    <label for="first_name">First Name</label>
    <input type="text" id="first_name" class="form-control" name="first_name" 
           value="<?php echo isset($userData['First_name']) ? $userData['First_name'] : ''; ?>" 
           oninput="generateEmail()" required>
</div>
            <div class="form-group">
                <label for="middle_name">Middle Name</label>
                <input type="text" class="form-control" name="middle_name" value="<?php echo isset($userData['Middle_name']) ? $userData['Middle_name'] : ''; ?>">
            </div>
            <div class="form-group">
    <label for="last_name">Last Name</label>
    <input type="text" id="last_name" class="form-control" name="last_name" 
           value="<?php echo isset($userData['Last_name']) ? $userData['Last_name'] : ''; ?>" 
           oninput="generateEmail()" required>
</div>
            <div class="form-group">
                <label for="gender">Gender</label>
                <select class="form-control" name="gender" required>
                    <option value="Male" <?php echo (isset($userData['Gender']) && $userData['Gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo (isset($userData['Gender']) && $userData['Gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo (isset($userData['Gender']) && $userData['Gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="birthday">Birthday</label>
                <input type="date" class="form-control" name="birthday" value="<?php echo isset($userData['Birthday']) ? $userData['Birthday'] : ''; ?>" required>
            </div>
            <div class="form-group">
    <label for="email">Email</label>
    <input type="text" id="email" class="form-control" name="email" 
           value="<?php echo isset($userData['User_email']) ? $userData['User_email'] : ''; ?>" 
           
           title="Email must end with @my.cspc.edu.ph">
</div>

<div class="form-group">
    <label for="contact_num">Contact Number</label>
    <input type="text" class="form-control" name="contact_num" id="contact_num"
           value="<?php echo isset($userData['Contact_num']) ? $userData['Contact_num'] : ''; ?>" 
           required 
           pattern="^\d{11,}$" 
           title="Contact number must be at least 11 digits long and contain only numbers.">
</div>

        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="user_type">User Type</label>
                <select class="form-control" name="user_type" onchange="classArmDropdown(this.value)" required>
                    <option value="">Select Type</option>
                    <?php
$typeQuery = "SELECT * FROM usertype_table WHERE Usertype_ID = 3";
$typeResult = $conn->query($typeQuery);
while ($typeRow = $typeResult->fetch_assoc()) {
    $selected = (isset($userData['Usertype_ID']) && $userData['Usertype_ID'] == $typeRow['Usertype_ID']) ? 'selected' : '';
    echo "<option value='" . $typeRow['Usertype_ID'] . "' $selected>" . $typeRow['Type_name'] . "</option>";
}
?>

                </select>
            </div>
            <div class="form-group">
                <label for="course_id">Course ID</label>
                <select class="form-control" name="course_id" required>
                    <option value="">Select Course</option>
                    <?php
                    $courseQuery = "SELECT * FROM course_table";
                    $courseResult = $conn->query($courseQuery);
                    while ($courseRow = $courseResult->fetch_assoc()) {
                        $selected = (isset($userData['Course_ID']) && $userData['Course_ID'] == $courseRow['Course_ID']) ? 'selected' : '';
                        echo "<option value='" . $courseRow['Course_ID'] . "' $selected>" . $courseRow['Course_name'] . "</option>";
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
                        $selected = (isset($userData['Year_ID']) && $userData['Year_ID'] == $yearRow['Year_ID']) ? 'selected' : '';
                        echo "<option value='" . $yearRow['Year_ID'] . "' $selected>" . $yearRow['Year_name'] . "</option>";
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
                        $selected = (isset($userData['Section_ID']) && $userData['Section_ID'] == $sectionRow['Section_ID']) ? 'selected' : '';
                        echo "<option value='" . $sectionRow['Section_ID'] . "' $selected>" . $sectionRow['Section_name'] . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" class="form-control" name="address" value="<?php echo isset($userData['Address']) ? $userData['Address'] : ''; ?>" required>
            </div>
            <!-- <div class="form-group">
                <label for="pin">PIN</label>
                <input type="number" class="form-control" name="pin" value="<?php echo isset($userData['PIN']) ? $userData['PIN'] : ''; ?>" required>
            </div> -->
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" name="password" placeholder="Leave blank to keep current password">
            </div>
        </div>
    </div>
    <button type="submit" name="save" class="btn btn-primary btn-lg btn-block">Save</button>
</form>
                                </div>
                            </div>
                        </div>
                    </div>

                <!-- Container Fluid -->
            </div>
            <!-- Footer -->
        </div>
    </div>

    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <script>
    function generateEmail() {
        var firstName = document.getElementById('first_name').value.trim();
        var lastName = document.getElementById('last_name').value.trim();
        var emailField = document.getElementById('email');

        // Check if both first name and last name are provided
        if (firstName && lastName) {
            // Get the first two letters of the first name and append the last name
            var emailLocalPart = firstName.substring(0, 10).toLowerCase() + lastName.toLowerCase();
            var email = emailLocalPart + "@my.cspc.edu.ph";

            // If email field is not empty or if it contains part of the domain, don't overwrite
            if (emailField.value.indexOf('@my.cspc.edu.ph') === -1) {
                emailField.value = email;  // Update the email value with generated username + domain
            }
        }
    }
</script>   
  
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
      $('#dataTable').DataTable(); // ID From dataTable 
      $('#dataTableHover').DataTable(); // ID From dataTable with Hover
    });
  </script>
</body>
</html>
