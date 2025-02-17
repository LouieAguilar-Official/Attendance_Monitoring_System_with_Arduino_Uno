<?php 
error_reporting(E_ALL); // Enable all error reporting for debugging
include '../Includes/dbcon.php';
include '../Includes/session.php';



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

    <style>
        /* Your existing styles */
        .main-container { margin-top: 5px; }
        .card { border: 1px solid #ddd; border-radius: 8px; box-shadow: 0px 0px 3px rgba(0, 0, 0, 0.05); font-size: 0.75rem; }
        .card-header { padding: 0.25rem 0.5rem; font-size: 0.875rem; background-color: #f8f9fc; }
        .breadcrumb { font-size: 0.65rem; }
        h1, h6 { font-size: 0.875rem; }
        .form-group label { font-size: 0.6rem; }
        .form-control { font-size: 0.8rem; }
        /* Additional styles here */
    </style>
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
                        <h1 class="h3 mb-0 text-gray-800">Edit Profile</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="./">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page"><a href="profile.php">Profile</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit Profile</li>
                        </ol>
                    </div>
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
                <input type="email" class="form-control" name="email" value="<?php echo isset($userData['User_email']) ? $userData['User_email'] : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="contact_num">Contact Number</label>
                <input type="text" class="form-control" name="contact_num" value="<?php echo isset($userData['Contact_num']) ? $userData['Contact_num'] : ''; ?>" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="user_type">User Type</label>
                <select class="form-control" name="user_type" onchange="classArmDropdown(this.value)" required>
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
            <div class="form-group">
                <label for="pin">PIN</label>
                <input type="number" class="form-control" name="pin" value="<?php echo isset($userData['PIN']) ? $userData['PIN'] : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" name="password" placeholder="Leave blank to keep current password">
            </div>
        </div>
    </div>
    <button type="submit" name="save" class="btn btn-primary btn-lg btn-block">Save</button>
</form>
                    <!---Container Fluid-->
                </div>
            </div>
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
            $('#dataTable').DataTable(); // ID From dataTable 
            $('#dataTableHover').DataTable(); // ID From dataTable with Hover
        });
    </script>
</body>
</html>
