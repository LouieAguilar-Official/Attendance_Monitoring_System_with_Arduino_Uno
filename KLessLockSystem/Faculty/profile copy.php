<?php
error_reporting(0);
session_start(); // Start the session
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Assuming the user ID is stored in a session variable after login
$userId = $_SESSION['userId'];

// Fetch the user's data from the user_table using a prepared statement
$query = "SELECT First_name, Middle_name, Last_name, Gender, User_email, Birthday, Address, Contact_num, PIN FROM user_table WHERE User_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$rs = $stmt->get_result();

if ($rs && $rs->num_rows > 0) {
    $user = $rs->fetch_assoc();
    $middleInitial = !empty($user['Middle_name']) ? strtoupper(substr($user['Middle_name'], 0, 1)) . '.' : '';
    $userName = htmlspecialchars($user['First_name']) . ' ' . $middleInitial . ' ' . htmlspecialchars($user['Last_name']);
} else {
    $userName = "Unknown User"; // Fallback in case user data is not found
}

$stmt->close();
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
        .main-container {
            margin-top: 5px;
        }

        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0px 0px 3px rgba(0, 0, 0, 0.05);
            font-size: 0.75rem;
        }

        .card-header {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            background-color: #f8f9fc;
        }

        .breadcrumb {
            font-size: 0.625rem;
        }

        h1, h6 {
            font-size: 0.875rem;
        }

        .profile-section {
            text-align: center;
            border-right: 1px solid #ddd;
            padding-right: 20px;
        }

        .profile-image {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 10px;
            border: 2px solid #ccc;
            border-radius: 50%;
            overflow: hidden;
        }

        .profile-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .edit-button {
            background-color: #4caf50;
            color: #ffffff;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 5px;
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
        }

        .data-section {
            padding: 0 20px;
        }

        .data-header h3 {
            background-color: #467fcf;
            color: #ffffff;
            padding: 10px;
            margin: 0 0 15px;
        }

        .data-list {
            list-style-type: none;
            padding: 0;
        }

        .data-list li {
            margin: 10px 0;
            font-size: 1em;
        }

        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .action-buttons button {
            background-color: #4caf50;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            font-size: 1em;
            cursor: pointer;
            border-radius: 5px;
        }

        .action-buttons .edit-info {
            background-color: #467fcf;
        }
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
                        <h1 class="h3 mb-0 text-gray-800">Profile Setting</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="./">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Profile</li>
                        </ol>
                    </div>

                    <div class="row">
                        <!-- Profile Section -->
                        <div class="col-lg-4 profile-section">
                            <div class="profile-image">
                                <img src="https://via.placeholder.com/150" alt="Profile Picture">
                                <button class="edit-button">Edit</button>
                            </div>
                            <h2><?php echo $userName; ?></h2>
                        </div>

                        <!-- Data Section -->
                        <div class="col-lg-8 data-section">
                            <div class="data-header">
                                <h3>Data</h3>
                            </div>

                            <?php
                            // Fetch the user's data
                            $query = "SELECT * FROM user_table WHERE User_ID = ?";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("i", $userId);
                            $stmt->execute();
                            $rs = $stmt->get_result();

                            if ($rs && $rs->num_rows > 0) {
                                $student = $rs->fetch_assoc();
                                echo '<ul class="data-list">';
                                echo '<li>School ID number: ' . htmlspecialchars($student['School_ID']) . '</li>';
                                echo '<li>Name: ' . htmlspecialchars($student['First_name']) . ' ' . $middleInitial . ' ' . htmlspecialchars($student['Last_name']) . '</li>';
                                echo '<li>Gender: ' . htmlspecialchars($student['Gender']) . '</li>';
                                echo '<li>Email: ' . htmlspecialchars($student['User_email']) . '</li>';
                                echo '<li>Birthday: ' . htmlspecialchars($student['Birthday']) . '</li>';
                                echo '<li>Address: ' . htmlspecialchars($student['Address']) . '</li>';
                                echo '<li>Contact Num: ' . htmlspecialchars($student['Contact_num']) . '</li>';
                                echo '<li>PIN:  </li>';
                                echo '</ul>';
                            } else {
                                echo '<div class="alert alert-danger" role="alert">No Record Found!</div>';
                            }

                            $stmt->close();
                            ?>

                            <div class="action-buttons">
                                <a href="change_pin.php" class="btn btn-success change-pin">Change PIN</a>
                                <!-- <a href="edit_user.php" class="btn btn-primary edit-info">Edit Info</a> -->
                            </div>

                        </div>
                    </div>
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
