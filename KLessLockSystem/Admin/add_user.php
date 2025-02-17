<?php 
error_reporting(E_ALL); // Enable all error reporting for debugging
include '../Includes/dbcon.php';
include '../Includes/session.php';

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
  
    // **Revised Email Validation:**
    $isValidEmail = true; // Flag to track email validity
  
    if (empty($email)) {
       
        $statusMsg = "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
        Email is required.
       <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
           <span aria-hidden='true'>&times;</span>
       </button>
     </div>";
        $isValidEmail = false;
      } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        
        $statusMsg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'><i class='fas fa-exclamation-circle'></i>
         <i class='fas fa-exclamation-circle'></i> Invalid email format. Please enter a valid email address.
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
            <span aria-hidden='true'>&times;</span>
        </button>
      </div>";
        $isValidEmail = false;
      } else if (!str_ends_with($email, "@my.cspc.edu.ph")) {
        
        $statusMsg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'><i class='fas fa-exclamation-circle'></i>
         Invalid email address. Please enter your username followed by @my.cspc.edu.ph
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
            <span aria-hidden='true'>&times;</span>
        </button>
      </div>";
        $isValidEmail = false;
      }
  
    // Process form data only if email is valid
    if ($isValidEmail) {
      $sampPass = $_POST['password'];
      $password = md5($sampPass);
      $course_id = ($user_type == 1 || $user_type == 2) ? "NULL" : $_POST['course_id'];
      $year_id = ($user_type == 1 || $user_type == 2) ? "NULL" : $_POST['year_id'];
      $section_id = ($user_type == 1 || $user_type == 2) ? "NULL" : $_POST['section_id'];
  
      $query = "INSERT INTO user_table (`School_ID`, `First_name`, `Middle_name`, `Last_name`, `Gender`, 
                `Birthday`, `Address`, `Contact_num`, `Usertype_ID`, `Course_ID`, `Year_ID`, 
                `Section_ID`, `User_email`, `password`) 
                VALUES ('$school_id', '$first_name', '$middle_name', '$last_name', '$gender', 
                '$birthday', '$address', '$contact_num', '$user_type', $course_id, 
                $year_id, $section_id, '$email', '$password')";
  
      if (mysqli_query($conn, $query)) {
        $statusMsg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
        Created Successfully!
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
            <span aria-hidden='true'>&times;</span>
        </button>
      </div>";
      } else {
       
        $statusMsg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
        An error occurred: " . mysqli_error($conn) . "
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
            <span aria-hidden='true'>&times;</span>
        </button>
    </div>";
      }
    }
  }
  

if (isset($_POST['update'])) {
    $user_id = $_GET['User_ID'];
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
    
    // Set course_id, year_id, section_id to NULL for user types 1 and 2
    $course_id = ($user_type == 1 || $user_type == 2) ? "NULL" : $_POST['course_id'];
    $year_id = ($user_type == 1 || $user_type == 2) ? "NULL" : $_POST['year_id'];
    $section_id = ($user_type == 1 || $user_type == 2) ? "NULL" : $_POST['section_id'];

    // Update the user data
    $updateQuery = "UPDATE user_table SET `School_ID` = '$school_id', `First_name` = '$first_name', `Middle_name` = '$middle_name', 
                    `Last_name` = '$last_name', `Gender` = '$gender', `Birthday` = '$birthday', `Address` = '$address',
                    `Contact_num` = '$contact_num', `Usertype_ID` = '$user_type', `Course_ID` = $course_id, 
                    `Year_ID` = $year_id, `Section_ID` = $section_id, `User_email` = '$email', `password` = '$password'
                    WHERE `User_ID` = '$user_id'";

    if (mysqli_query($conn, $updateQuery)) {
        
        $statusMsg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
        Updated Successfully!
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
            <span aria-hidden='true'>&times;</span>
        </button>
      </div>";
    } else {
        
        $statusMsg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
        An error occurred: " . mysqli_error($conn) . "
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
            <span aria-hidden='true'>&times;</span>
        </button>
    </div>";
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
        $statusMsg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
        User and related records deleted successfully!
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
            <span aria-hidden='true'>&times;</span>
        </button>
    </div>";

    } catch (Exception $e) {
        // Rollback the transaction if something failed
        mysqli_rollback($conn);
       
        $statusMsg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
        Error deleting user: " . $e->getMessage() . "
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
            <span aria-hidden='true'>&times;</span>
        </button>
    </div>";
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
                        <h1 class="h3 mb-0 text-gray-800">Add User</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="./">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Add User</li>
                        </ol>
                    </div>
                    <?php if (isset($statusMsg)) : ?>
                                        <?php echo $statusMsg; ?>
                                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="m-0 font-weight-bold text-primary">User Details</h5>
                                </div>
                                <div class="card-body">
                                   

                                    
 <form method="post">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="school_id">School ID</label>
                <input type="text" class="form-control" name="school_id" value="<?php echo isset($userData['School_ID']) ? $userData['School_ID'] : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" class="form-control" name="first_name" value="<?php echo isset($userData['First_name']) ? $userData['First_name'] : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="middle_name">Middle Name</label>
                <input type="text" class="form-control" name="middle_name" value="<?php echo isset($userData['Middle_name']) ? $userData['Middle_name'] : ''; ?>">
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" class="form-control" name="last_name" value="<?php echo isset($userData['Last_name']) ? $userData['Last_name'] : ''; ?>" required>
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
  <input type="email" class="form-control" name="email" required>
  <span class="validation-message">Invalid email format. Must end with @my.cspc.edu.ph</span>
</div>

<script>
const emailInput = document.querySelector('input[name="email"]');
const validationMessage = document.querySelector('.validation-message');

emailInput.addEventListener('input', (event) => {
  const email = event.target.value;
  if (email.trim() === '' || !email.endsWith('@my.cspc.edu.ph')) {
    validationMessage.style.display = 'block';
  } else {
    validationMessage.style.display = 'none';
  }
});
</script>
           
        </div>
        <div class="col-md-6">
        <div>
    <label for="contact_num">Contact Number</label>
    <input 
        type="text" 
        class="form-control" 
        id="contact_num" 
        name="contact_num" 
        value="<?php echo isset($userData['Contact_num']) ? $userData['Contact_num'] : ''; ?>" 
        required 
        oninput="validateAndFormatContactNumber(this)"
        placeholder="e.g., 9123456789"
    >
</div>

<script>
    function validateAndFormatContactNumber(input) {
        let value = input.value;

        // Remove all non-numeric characters except '+'
        value = value.replace(/[^0-9+]/g, '');

        // Check if it starts with +63 or 0
        if (!value.startsWith('+63') && !value.startsWith('0')) {
            alert('The contact number must start with +63 or 0.');
            input.value = '';
            return;
        }

        // Replace leading 0 with +63 for proper format
        if (value.startsWith('0')) {
            value = value.replace(/^0/, '+63');
        }

        // Ensure the length of the number is 14 characters including +63 (i.e., +63912345678901)
        if (value.length > 14) {
            value = value.slice(0, 14);
        }

        // Ensure exactly 13 digits after the country code (+63)
        if (value.length === 14 && !/^(\+63)\d{11}$/.test(value)) {
            alert('The contact number must be 13 digits long, starting with +63.');
            input.value = '';
            return;
        }

        input.value = value;
    }
</script>


            <div class="form-group"> 
    <label for="user_type">User Type</label>
    <select class="form-control" name="user_type" id="user_type" onchange="toggleDropdowns()" required>
        <option value="">Select Type</option>
        <?php
        $typeQuery = "SELECT * FROM usertype_table";
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
    <select class="form-control" name="course_id" id="course_id" required>
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
    <select class="form-control" name="year_id" id="year_id" required>
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
    <select class="form-control" name="section_id" id="section_id" required>
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
    <input type="password" class="form-control" name="password" placeholder="Enter a password" required>
</div>

        </div>
    </div>
    <?php if (isset($_GET['action']) && $_GET['action'] == 'edit'): ?>
        <button type="submit" name="update" class="btn btn-info btn-lg btn-block">Update</button>

    <?php else: ?>
        <button type="submit" name="save" class="btn btn-primary btn-lg btn-block">Save</button>
    <?php endif; ?>
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
                <h6 class="m-0 font-weight-bold text-primary">User List</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                <table class="table align-items-center table-flush table-hover" id="dataTableHover">
    <thead class="thead-light">
        <tr>
            <th>User ID</th>
            <th>School Number</th>
            <th>Name</th>
            <th>Email</th>
            <th>User Type</th>
            <th>Contact Number</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php
    // Fetch all students from the user_table, ordered by User_ID DESC
    $studentQuery = "SELECT u.*, ut.Type_name 
                    FROM user_table u 
                    JOIN usertype_table ut ON u.Usertype_ID = ut.Usertype_ID
                    ORDER BY u.User_ID DESC"; // Order by User_ID in descending order
    $studentResult = $conn->query($studentQuery);

    // Loop through the results and display them
    while ($studentRow = $studentResult->fetch_assoc()) {
        // Capitalize the first name and last name
        $firstName = ucfirst(strtolower($studentRow['First_name']));
        $lastName = ucfirst(strtolower($studentRow['Last_name']));

        echo "<tr>
                <td>{$studentRow['User_ID']}</td> <!-- Display User ID here -->
                <td>{$studentRow['School_ID']}</td> <!-- Display School Number -->
                <td>{$firstName} {$lastName}</td> <!-- Display Name -->
                <td>{$studentRow['User_email']}</td> <!-- Display Email -->
                <td>{$studentRow['Type_name']}</td> <!-- Display User Type -->
                <td>{$studentRow['Contact_num']}</td> <!-- Display Contact Number -->
                <td>
                    <a href='?User_ID={$studentRow['User_ID']}&action=edit' class='btn btn-info btn-sm'>
                        <i class='fas fa-edit'></i>
                    </a>
                    <a href='?User_ID={$studentRow['User_ID']}&action=delete' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this student?\");'>
                        <i class='fas fa-trash'></i>
                    </a>
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
                <!-- Container Fluid -->
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
