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
    <link href="img/logo/attnlg.jpg" rel="icon">
    <title>All User</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="css/ruang-admin.min.css" rel="stylesheet">
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function () {
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
                        <h1 class="h3 mb-0 text-gray-800">All User</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="./">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">User List</li>
                        </ol>
                    </div>

                    <!-- Display User Data -->
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Student List</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>School Number</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>User Type</th>
                                                    <th>Contact Number</th>
                                                    <!-- <th>Action</th> -->
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                                // Fetch users with the same User Type, Course, Section, and Year
                                                $userQuery = "
                                                    SELECT u.*, ut.Type_name 
                                                    FROM user_table u 
                                                    JOIN usertype_table ut ON u.Usertype_ID = ut.Usertype_ID 
                                                    WHERE (u.Usertype_ID, u.Course_ID, u.Section_ID, u.Year_ID) IN (
                                                        SELECT Usertype_ID, Course_ID, Section_ID, Year_ID 
                                                        FROM user_table 
                                                        GROUP BY Usertype_ID, Course_ID, Section_ID, Year_ID 
                                                        HAVING COUNT(*) > 1
                                                    ) 
                                                    ORDER BY u.Usertype_ID, u.Course_ID, u.Section_ID, u.Year_ID";
                                                
                                                $userResult = $conn->query($userQuery);

                                                // Loop through the results and display them
                                                while ($userRow = $userResult->fetch_assoc()) {
                                                    echo "<tr>
                                                        <td>{$userRow['School_ID']}</td>
                                                        <td>{$userRow['First_name']} {$userRow['Last_name']}</td>
                                                        <td>{$userRow['User_email']}</td>
                                                        <td>{$userRow['Type_name']}</td>
                                                        <td>{$userRow['Contact_num']}</td>
                                                       
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
    </div>

    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/ruang-admin.min.js"></script>
</body>
</html>
