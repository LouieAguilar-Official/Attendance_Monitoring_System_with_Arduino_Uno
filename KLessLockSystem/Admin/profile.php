<?php
error_reporting(E_ALL);

// Includes
include '../Includes/dbcon.php'; // Database connection
include '../Includes/session.php'; // Session handler

// Check if user is logged in
if (!isset($_SESSION['userId'])) {
    die("Access denied. Please log in first.");
}

// Fetch User ID from session
$userId = $_SESSION['userId'];

// Ensure database connection is valid
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch user data
$query = "SELECT * FROM user_table WHERE User_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$rs = $stmt->get_result();

// Check if user data exists
if ($rs && $rs->num_rows > 0) {
    $user = $rs->fetch_assoc();
    $middleInitial = !empty($user['Middle_name']) ? strtoupper(substr($user['Middle_name'], 0, 1)) . '.' : '';
    $userName = htmlspecialchars($user['First_name']) . ' ' . $middleInitial . ' ' . htmlspecialchars($user['Last_name']);
    $firstName = htmlspecialchars($user['First_name']);
    $lastName = htmlspecialchars($user['Last_name']);
    $email = htmlspecialchars($user['User_email']);
    $schoolId = htmlspecialchars($user['School_ID']);
    $address = htmlspecialchars($user['Address']);
    $birthday = htmlspecialchars($user['Birthday']);
    $contactNum = htmlspecialchars($user['Contact_num']);
} else {
    $user = null;
    $userName = "Unknown User";
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="img/logo/qc.png" rel="icon">
  <title>Dashboard</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
  <style>
  .circle-avatar {
    width: 130px;
    height: 130px;
    background-color: #4bb8bc;
    border-radius: 50%;
    margin: 28px auto 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
  }

  .circle-avatar span {
    font-size: 2.5rem;
    font-weight: bold;
  }

  .name {
    text-align: center;
    margin-top: 10px;
    font-size: 1.1rem;
  }

  .email {
    text-align: center;
    font-size: 1rem;
    color: #6c757d;
    margin-top: 2px;
    font-weight: bold;
    border-bottom: 1px solid #d4d1d2;
    padding-bottom: 5px;
  }

  .card {
    border: 0.5px solid #d4d1d2;
    border-radius: 10px;
    padding: 15px;
  }

  .icon-text-table {
    margin-top: 20px;
    width: 100%;
    border-collapse: collapse;
  }

  .icon-text-table td {
    padding: 8px;
    vertical-align: middle;
    font-size: 1rem;
    color: #6c757d;
  }

  .icon-text-table td i {
    margin-right: 10px;
    color: #6c757d;
    font-size: 20px;
  }

  .icon-text-table tr:not(:last-child) {
    border-bottom: 1px solid #d4d1d2;
  }
</style>
</head>
<body id="page-top">
  <div id="wrapper">
    <?php include "Includes/sidebar.php"; ?>
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <?php include "Includes/topbar.php"; ?>
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Account</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
          </div>

          <div class="row">
            <div class="col-lg-4">
              <div class="card">
                <div class="card-body p-2">
                  <div class="circle-avatar">
                    <span><?php echo strtoupper($firstName[0] . $lastName[0]); ?></span>
                  </div>

                  <div class="name">
                    <p><h3><?php echo ucwords(strtolower($userName)); ?></h3></p>
                  </div>

                  <div class="email">
                    <p><?php echo $email; ?></p>
                  </div>

                  <div class="school-id">
                    <table class="icon-text-table">
                      <tr>
                        <td><i class="fas fa-id-card"></i> School ID: <?php echo $schoolId; ?></td>
                      </tr>
                    </table>
                  </div>

                  <div class="address">
                    <table class="icon-text-table">
                      <tr>  
                        <td><i class="fas fa-map-marker-alt"></i> Address: <?php echo $address; ?></td>
                      </tr>
                    </table>
                  </div>

                  <div class="birthday">
                    <table class="icon-text-table">
                      <tr>
                      <td><i class="fas fa-birthday-cake"></i> Birthday: <?php echo date("F j, Y", strtotime($birthday)); ?></td>

                      </tr>
                    </table>
                  </div>

                  <div class="contact-number">
                    <table class="icon-text-table">
                      <tr>
                        <td><i class="fas fa-phone"></i> Contact Number: <?php echo $contactNum; ?></td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>
              <div class="d-flex justify-content-center mt-4">
                <button class="btn btn-primary w-100">Edit Profile</button>
              </div>
            </div>

            <div class="col-lg-8">
  <div class="card shadow-sm">
    <div class="card-body">
      <h4 class="card-title text-primary"><i class="fas fa-info-circle"></i> User Dashboard Overview</h4>
      
      <div class="row mt-3">
        <div class="col-md-6">
          <div class="p-3 bg-light rounded shadow-sm">
            <h5><i class="fas fa-clock text-warning"></i> Last Login</h5>
            <p class="text-muted"><?php echo date("F j, Y - g:i A"); ?></p>
          </div>
        </div>
        <div class="col-md-6">
          <div class="p-3 bg-light rounded shadow-sm">
            <h5><i class="fas fa-tasks text-success"></i> Account Status</h5>
            <p class="text-muted">Active</p>
          </div>
        </div>
      </div>

      <div class="mt-4">
        <h5><i class="fas fa-bell text-danger"></i> Announcements</h5>
        <ul class="list-group">
          <li class="list-group-item d-flex justify-content-between align-items-center">
            New school year starts soon!
            <span class="badge badge-primary badge-pill">1 day ago</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            Update your profile for better security.
            <span class="badge badge-success badge-pill">3 days ago</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            Check out the latest school events.
            <span class="badge badge-warning badge-pill">1 week ago</span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

          </div>

        </div>
      </div>
    </div>
  </div>

  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
  <script src="../vendor/chart.js/Chart.min.js"></script>
  <script src="js/demo/chart-area-demo.js"></script>
</body>
</html>
