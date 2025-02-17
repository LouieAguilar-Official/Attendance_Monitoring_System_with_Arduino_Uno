<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

//------------------------SAVE--------------------------------------------------

if(isset($_POST['save'])){
    
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $emailAddress = $_POST['emailAddress'];
    $phoneNo = $_POST['phoneNo'];
    $classId = $_POST['classId'];
    $classArmId = $_POST['classArmId'];
    $dateCreated = date("Y-m-d");
   
    // Check if email already exists
    $query = mysqli_query($conn, "SELECT * FROM tblclassteacher WHERE emailAddress = '$emailAddress'");
    $ret = mysqli_fetch_array($query);

    $sampPass = "pass123";
    $sampPass_2 = md5($sampPass);

    if ($ret > 0) {
        $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>This Email Address Already Exists!</div>";
    } else {
        // Insert into database
        $query = mysqli_query($conn, "INSERT INTO tblclassteacher (firstName, lastName, emailAddress, password, phoneNo, classId, classArmId, dateCreated) 
        VALUES ('$firstName', '$lastName', '$emailAddress', '$sampPass_2', '$phoneNo', '$classId', '$classArmId', '$dateCreated')");

        if ($query) {
            $qu = mysqli_query($conn, "UPDATE tblclassarms SET isAssigned = '1' WHERE Id = '$classArmId'");
            if ($qu) {
                $statusMsg = "<div class='alert alert-success' style='margin-right:700px;'>Created Successfully!</div>";
            } else {
                $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error occurred while assigning class!</div>";
            }
        } else {
            $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error occurred while creating teacher!</div>";
        }
    }
}

//------------------------EDIT/UPDATE--------------------------------------------------

if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "edit") {
    $Id = $_GET['Id'];

    // Fetch the existing data for the teacher
    $query = mysqli_query($conn, "SELECT * FROM tblclassteacher WHERE Id = '$Id'");
    $row = mysqli_fetch_array($query);

    // If the form is submitted to update
    if (isset($_POST['update'])) {
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $emailAddress = $_POST['emailAddress'];
        $phoneNo = $_POST['phoneNo'];
        $classId = $_POST['classId'];
        $classArmId = $_POST['classArmId'];

        // Update the class teacher details
        $query = mysqli_query($conn, "UPDATE tblclassteacher SET 
            firstName = '$firstName', 
            lastName = '$lastName',
            emailAddress = '$emailAddress', 
            phoneNo = '$phoneNo', 
            classId = '$classId', 
            classArmId = '$classArmId'
            WHERE Id = '$Id'");

        if ($query) {
            echo "<script type='text/javascript'>
                window.location = 'createClassTeacher.php';
            </script>"; 
        } else {
            $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error occurred while updating!</div>";
        }
    }
}

//--------------------------------DELETE--------------------------------------------------

if (isset($_GET['Id']) && isset($_GET['classArmId']) && isset($_GET['action']) && $_GET['action'] == "delete") {
    $Id = $_GET['Id'];
    $classArmId = $_GET['classArmId'];

    // Delete the class teacher
    $query = mysqli_query($conn, "DELETE FROM tblclassteacher WHERE Id = '$Id'");

    if ($query == TRUE) {
        // Reset the classArm assignment
        $qu = mysqli_query($conn, "UPDATE tblclassarms SET isAssigned = '0' WHERE Id = '$classArmId'");
        if ($qu) {
            echo "<script type='text/javascript'>
                window.location = 'createClassTeacher.php';
            </script>";
        } else {
            $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error occurred while resetting class assignment!</div>";
        }
    } else {
        $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error occurred while deleting!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="img/logo/attnlg.jpg" rel="icon">
  <title>Dashboard</title>
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
        <div class="container">
          <div class="header">
            <div class="breadcrumbs">
              <p><a href="#">Faculty</a> > <a href="#">Faculty Information</a></p>
            </div>
            <div class="add-btn">
              <a href="createUsers.php" class="btn-add">Add New</a>
            </div>
          </div>

          <!-- Input Group -->
          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">All Faculty</h6>
                </div>
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email Address</th>
                        <th>Edit</th>
                        <th>Delete</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $query = "SELECT Id, firstName, lastName, emailAddress FROM tblclassteacher";
                      $rs = $conn->query($query);
                      $num = $rs->num_rows;
                      $sn = 0;
                      if ($num > 0) {
                        while ($rows = $rs->fetch_assoc()) {
                          $sn++;
                          echo "
                            <tr>
                              <td>$sn</td>
                              <td>{$rows['firstName']}</td>
                              <td>{$rows['lastName']}</td>
                              <td>{$rows['emailAddress']}</td>
                              <td><a href='?action=edit&Id={$rows['Id']}'><i class='fas fa-fw fa-edit'></i></a></td>
                              <td><a href='?action=delete&Id={$rows['Id']}&classArmId={$rows['classArmId']}' onclick='return confirm(\"Are you sure you want to delete this record?\")'><i class='fas fa-fw fa-trash'></i></a></td>
                            </tr>";
                        }
                      } else {
                        echo "<div class='alert alert-danger' role='alert'>No Record Found!</div>";
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
  </div>

  <!-- Scroll to top -->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
</body>
</html>
