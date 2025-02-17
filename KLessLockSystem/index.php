<?php  
include 'Includes/dbcon.php';
session_start();

// Check if the login form is submitted
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate inputs
    if (empty($username) || empty($password)) {
        echo "<div class='alert alert-danger' role='alert'>Please fill in all fields!</div>";
        exit;
    }

    // Hash the password using MD5 (consider using a stronger hashing algorithm in production)
    $hashedPassword = md5($password);

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM user_table WHERE User_email = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Check if the hashed password matches
        if ($row['password'] === $hashedPassword) {
            // Set session variables
            $_SESSION['userId'] = $row['User_ID'];
            $_SESSION['firstName'] = $row['First_name'];
            $_SESSION['lastName'] = $row['Last_name'];
            $_SESSION['emailAddress'] = $row['User_email'];

            // Redirect based on user type
            $userTypeId = $row['Usertype_ID'];
            switch ($userTypeId) {
                case 1: // Admin
                    echo "<script type=\"text/javascript\">window.location = \"Admin/index.php\";</script>";
                    break;
                case 2: // Faculty
                    echo "<script type=\"text/javascript\">window.location = \"Faculty/index.php\";</script>";
                    break;
                case 3: // Student
                    echo "<script type=\"text/javascript\">window.location = \"Student/index.php\";</script>";
                    break;
                default:
                    echo "<div class='alert alert-danger' role='alert'>Invalid User Type!</div>";
                    break;
            }
        } else {
            echo "<div class='alert alert-danger' role='alert'>Invalid Username/Password!</div>";
        }
    } else {
        echo "<div class='alert alert-danger' role='alert'>Invalid Username/Password!</div>";
    }

    $stmt->close();
}
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
            <title>klesslock - Login</title>
            <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
            <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
            <link href="css/ruang-admin.min.css" rel="stylesheet">
           
        </head>

        <!DOCTYPE html>
<html lang="en">

<head>
    <style>
        body {
            background: url('img/logo/background.jpg') no-repeat center center fixed;
            background-size: cover; /* Ensures full coverage */
            /* Optional: Add a fallback background color if the image fails to load */
            /* background-color: #f8f9fc; Example light gray */
        }

        /* Other styles (if needed) */
        .container-login {
            /* Styles for your login container */
        }
        .card{
            background-color: rgba(255, 255, 255, 0.8); /* Add some transparency to the card */
        }
        .white-text {
        color: white;
    }
    </style>
</head>

<body><div class="container-login">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12 col-md-9">
            <div class="card shadow-sm my-5" style="background-color: rgba(255, 255, 255, 0.2);">
                <div class="card-body p-9">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="login-form">
                            <h5 align="center" class="white-text">KLESS LOCK SYSTEM LOGIN</h5>

                                <div class="text-center">
                                    <img src="img/logo/kl.png" style="width:150px;height:150px" alt="Logo">
                                    <br><br>
                                </div>

                                <form class="user" method="POST" action="">
                                    <div class="form-group">
                                        <input type="text" class="form-control" required name="username"
                                            id="exampleInputEmail" placeholder="Enter Email Address">
                                    </div>
                                    <div class="form-group">
                                        <input type="password" name="password" required class="form-control"
                                            id="exampleInputPassword" placeholder="Enter Password">
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox small" style="line-height: 1.5rem;">
                                            <input type="checkbox" class="custom-control-input" id="customCheck">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <input type="submit" class="btn btn-primary btn-block" value="Login" name="login" />
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/ruang-admin.min.js"></script>
</body>

</html>
        </html>
