<?php 
error_reporting(E_ALL); // Set error reporting to display all errors
include '../Includes/dbcon.php';
include '../Includes/session.php';

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

//------------------------SAVE--------------------------------------------------

if (isset($_POST['save'])) {
    // Sanitize and validate input
    $firstName = sanitizeInput($_POST['firstName']);
    $lastName = sanitizeInput($_POST['lastName']);
    $otherName = sanitizeInput($_POST['otherName']);
    $emailAddress = filter_var(sanitizeInput($_POST['emailAddress']), FILTER_VALIDATE_EMAIL);
    $admissionNumber = sanitizeInput($_POST['admissionNumber']);
    $classId = sanitizeInput($_POST['classId']);
    $classArmId = sanitizeInput($_POST['classArmId']);
    $contactNumber = sanitizeInput($_POST['contactNumber']);
    $birthdate = sanitizeInput($_POST['birthdate']);
    $pin = sanitizeInput($_POST['pin']);
    $gender = sanitizeInput($_POST['gender']);
    $address = sanitizeInput($_POST['address']);
    $dateCreated = date("Y-m-d");
    $sampPass = $_POST['password'];
    
    // Password hashing
    $sampPass_2 = password_hash($sampPass, PASSWORD_DEFAULT);

    // Check for duplicate admission number
    $stmt = $conn->prepare("SELECT * FROM tblstudents WHERE admissionNumber = ?");
    $stmt->bind_param("s", $admissionNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) { 
        $statusMsg = "<div class='alert alert-danger'>This Admission Number Already Exists!</div>";
    } else {
        // Insert into tblstudents
        $stmt = $conn->prepare("INSERT INTO tblstudents (firstName, lastName, otherName, admissionNumber, emailAddress, password, classId, classArmId, contactNumber, birthdate, pin, gender, address, dateCreated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssisss", $firstName, $lastName, $otherName, $admissionNumber, $emailAddress, $sampPass_2, $classId, $classArmId, $contactNumber, $birthdate, $pin, $gender, $address, $dateCreated);
        
        if ($stmt->execute()) {
            // Insert into tblattendance
            $attendanceStmt = $conn->prepare("INSERT INTO tblattendance (admissionNo, classId, classArmId, sessionTermId, status, dateTimeTaken) VALUES (?, ?, ?, '1', '0', '')");
            $attendanceStmt->bind_param("sss", $admissionNumber, $classId, $classArmId);
            $attendanceStmt->execute();
            
            $statusMsg = "<div class='alert alert-success'>Created Successfully!</div>";
        } else {
            $statusMsg = "<div class='alert alert-danger'>An error occurred!</div>";
        }
    }
}

//---------------------------------------EDIT------------------------------------------------------------

if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "edit") {
    $Id = sanitizeInput($_GET['Id']);

    $stmt = $conn->prepare("SELECT * FROM tblstudents WHERE Id = ?");
    $stmt->bind_param("i", $Id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    //------------UPDATE-----------------------------
    if (isset($_POST['update'])) {
        $firstName = sanitizeInput($_POST['firstName']);
        $lastName = sanitizeInput($_POST['lastName']);
        $otherName = sanitizeInput($_POST['otherName']);
        $emailAddress = filter_var(sanitizeInput($_POST['emailAddress']), FILTER_VALIDATE_EMAIL);
        $admissionNumber = sanitizeInput($_POST['admissionNumber']);
        $classId = sanitizeInput($_POST['classId']);
        $classArmId = sanitizeInput($_POST['classArmId']);
        $contactNumber = sanitizeInput($_POST['contactNumber']);
        $birthdate = sanitizeInput($_POST['birthdate']);
        $pin = sanitizeInput($_POST['pin']);
        $gender = sanitizeInput($_POST['gender']);
        $address = sanitizeInput($_POST['address']);
        $dateCreated = date("Y-m-d");

        // Check if the password field is updated
        $sampPass = $_POST['password'];
        $sampPass_2 = $sampPass ? password_hash($sampPass, PASSWORD_DEFAULT) : $row['password']; // If no new password is provided, use the existing one.

        // Update query
        $stmt = $conn->prepare("UPDATE tblstudents SET firstName = ?, lastName = ?, otherName = ?, emailAddress = ?, admissionNumber = ?, password = ?, classId = ?, classArmId = ?, contactNumber = ?, birthdate = ?, pin = ?, gender = ?, address = ? WHERE Id = ?");
        $stmt->bind_param("ssssssssssisssi", $firstName, $lastName, $otherName, $emailAddress, $admissionNumber, $sampPass_2, $classId, $classArmId, $contactNumber, $birthdate, $pin, $gender, $address, $Id);

        if ($stmt->execute()) {
            echo "<script type='text/javascript'>window.location = ('student.php')</script>";
        } else {
            $statusMsg = "<div class='alert alert-danger'>An error occurred!</div>";
        }
    }
}

//--------------------------------DELETE------------------------------------------------------------------

if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "delete") {
    $Id = sanitizeInput($_GET['Id']);
    
    $stmt = $conn->prepare("DELETE FROM tblstudents WHERE Id = ?");
    $stmt->bind_param("i", $Id);
    
    if ($stmt->execute()) {
        echo "<script type='text/javascript'>window.location = ('student.php')</script>";
    } else {
        $statusMsg = "<div class='alert alert-danger'>An error occurred!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="img/logo/attnlg.jpg" rel="icon">
    <?php include 'includes/title.php'; ?>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="css/ruang-admin.min.css" rel="stylesheet">
    
    <script>
        function classArmDropdown(str) {
            if (str === "") {
                document.getElementById("txtHint").innerHTML = "";
                return;
            }
            const xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("txtHint").innerHTML = this.responseText;
                }
            };
            xmlhttp.open("GET", "ajaxClassArms2.php?cid=" + str, true);
            xmlhttp.send();
        }
    </script>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include "Includes/sidebar.php"; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include "Includes/topbar.php"; ?>

                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Create Students</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="./">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Create Students</li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Create Students</h6>
                                    <?php echo isset($statusMsg) ? $statusMsg : ''; ?>
                                </div>
                                <div class="card-body">
                                    <form method="post">
                                        <div class="form-group row mb-3">
                                            <div class="col-xl-6">
                                                <label class="form-control-label">Firstname<span class="text-danger ml-2">*</span></label>
                                                <input type="text" class="form-control" name="firstName" value="<?php echo isset($row['firstName']) ? $row['firstName'] : ''; ?>" required>
                                            </div>
                                            <div class="col-xl-6">
                                                <label class="form-control-label">Lastname<span class="text-danger ml-2">*</span></label>
                                                <input type="text" class="form-control" name="lastName" value="<?php echo isset($row['lastName']) ? $row['lastName'] : ''; ?>" required>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-3">
                                            <div class="col-xl-6">
                                                <label class="form-control-label">Othername</label>
                                                <input type="text" class="form-control" name="otherName" value="<?php echo isset($row['otherName']) ? $row['otherName'] : ''; ?>">
                                            </div>
                                            <div class="col-xl-6">
                                                <label class="form-control-label">Email<span class="text-danger ml-2">*</span></label>
                                                <input type="email" class="form-control" name="emailAddress" value="<?php echo isset($row['emailAddress']) ? $row['emailAddress'] : ''; ?>" required>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-3">
                                            <div class="col-xl-6">
                                                <label class="form-control-label">Admission Number<span class="text-danger ml-2">*</span></label>
                                                <input type="text" class="form-control" name="admissionNumber" value="<?php echo isset($row['admissionNumber']) ? $row['admissionNumber'] : ''; ?>" required>
                                            </div>
                                            <div class="col-xl-6">
                                                <label class="form-control-label">Password<span class="text-danger ml-2">*</span></label>
                                                <input type="password" class="form-control" name="password" <?php echo isset($row) ? '' : 'required'; ?>>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-3">
                                            <div class="col-xl-6">
                                                <label class="form-control-label">Class<span class="text-danger ml-2">*</span></label>
                                                <select class="form-control" name="classId" onchange="classArmDropdown(this.value)" required>
                                                    <option value="">Select Class</option>
                                                    <?php
                                                    $classQuery = "SELECT * FROM tblclass";
                                                    $classResult = $conn->query($classQuery);
                                                    while ($classRow = $classResult->fetch_assoc()) {
                                                        $selected = (isset($row['classId']) && $row['classId'] == $classRow['Id']) ? 'selected' : '';
                                                        echo "<option value='" . $classRow['Id'] . "' $selected>" . $classRow['className'] . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-xl-6">
                                                <label class="form-control-label">Class Arm<span class="text-danger ml-2">*</span></label>
                                                <select class="form-control" name="classArmId" id="txtHint" required>
                                                    <option value="">Select Class Arm</option>
                                                    <?php if (isset($row['classArmId'])): ?>
                                                        <option value="<?php echo $row['classArmId']; ?>" selected><?php echo $row['classArmId']; ?></option>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-3">
                                            <div class="col-xl-6">
                                                <label class="form-control-label">Contact Number<span class="text-danger ml-2">*</span></label>
                                                <input type="text" class="form-control" name="contactNumber" value="<?php echo isset($row['contactNumber']) ? $row['contactNumber'] : ''; ?>" required>
                                            </div>
                                            <div class="col-xl-6">
                                                <label class="form-control-label">Birthdate<span class="text-danger ml-2">*</span></label>
                                                <input type="date" class="form-control" name="birthdate" value="<?php echo isset($row['birthdate']) ? $row['birthdate'] : ''; ?>" required>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-3">
                                            <div class="col-xl-6">
                                                <label class="form-control-label">PIN<span class="text-danger ml-2">*</span></label>
                                                <input type="text" class="form-control" name="pin" value="<?php echo isset($row['pin']) ? $row['pin'] : ''; ?>" required>
                                            </div>
                                            <div class="col-xl-6">
                                                <label class="form-control-label">Gender<span class="text-danger ml-2">*</span></label>
                                                <select class="form-control" name="gender" required>
                                                    <option value="Male" <?php echo (isset($row['gender']) && $row['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                                    <option value="Female" <?php echo (isset($row['gender']) && $row['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-3">
                                            <div class="col-xl-12">
                                                <label class="form-control-label">Address<span class="text-danger ml-2">*</span></label>
                                                <textarea class="form-control" name="address" required><?php echo isset($row['address']) ? $row['address'] : ''; ?></textarea>
                                            </div>
                                        </div>
                                        <button type="submit" name="<?php echo isset($row) ? 'update' : 'save'; ?>" class="btn btn-primary btn-lg btn-block"><?php echo isset($row) ? 'Update' : 'Create'; ?></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Student List</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table align-items-center table-flush" id="dataTable">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Admission Number</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Contact Number</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $studentQuery = "SELECT * FROM tblstudents";
                                                $studentResult = $conn->query($studentQuery);
                                                while ($studentRow = $studentResult->fetch_assoc()) {
                                                    echo "<tr>
                                                            <td>{$studentRow['admissionNumber']}</td>
                                                            <td>{$studentRow['firstName']} {$studentRow['lastName']}</td>
                                                            <td>{$studentRow['emailAddress']}</td>
                                                            <td>{$studentRow['contactNumber']}</td>
                                                            <td>
                                                                <a href='?Id={$studentRow['Id']}&action=edit' class='btn btn-info btn-sm'>Edit</a>
                                                                <a href='?Id={$studentRow['Id']}&action=delete' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this student?\");'>Delete</a>
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
