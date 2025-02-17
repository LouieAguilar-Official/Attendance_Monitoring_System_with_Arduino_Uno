<?php 
error_reporting(E_ALL);
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Function to create a status entry
function createStatus($conn) {
    $status_name = $_POST['status_name'];

    // Check for duplicate Status_Name
    $checkQuery = "SELECT * FROM status_table WHERE Status_Name = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $status_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return false; // Duplicate Status_Name
    }

    $query = "INSERT INTO status_table (Status_Name) VALUES (?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $status_name);
    
    return $stmt->execute();
}

// Function to delete a status entry
function deleteStatus($conn, $statusId) {
    $query = "DELETE FROM status_table WHERE Status_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $statusId);
    return $stmt->execute();
}

// Function to edit status data
function editStatus($conn, $statusId) {
    if ($stmt = $conn->prepare("SELECT * FROM status_table WHERE Status_ID=?")) {
        $stmt->bind_param("i", $statusId);
        $stmt->execute();
        $result = $stmt->get_result();
        $statusData = $result->fetch_assoc();
        $stmt->close();
        return $statusData;
    }
    return null;
}

// Handle save, delete, and edit actions for statuses
$statusMsg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_status'])) {
        if (createStatus($conn)) {
            $statusMsg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
        Status Added Successfully!
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
            <span aria-hidden='true'>&times;</span>
        </button>
    </div>";
        } else {
            $statusMsg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
        Error: Duplicate Status Name.
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
            <span aria-hidden='true'>&times;</span>
        </button>
    </div>";
        }
    } elseif (isset($_POST['edit_status'])) {
        $status_id = $_POST['status_id'];
        $status_name = $_POST['status_name'];

        $query = "UPDATE status_table SET Status_Name=? WHERE Status_ID=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $status_name, $status_id);
        
        if ($stmt->execute()) {
            $statusMsg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
            Status Updated Successfully!
            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                <span aria-hidden='true'>&times;</span>
            </button>
        </div>";
        } else {
            $statusMsg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
        Error updating status: " . $stmt->error . "
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
            <span aria-hidden='true'>&times;</span>
        </button>
    </div>";
        }
        $stmt->close();
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['Status_ID'])) {
    $statusId = $_GET['Status_ID'];
    if (deleteStatus($conn, $statusId)) {
        
        $statusMsg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
        Status Deleted Successfully!
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
            <span aria-hidden='true'>&times;</span>
        </button>
    </div>";
    } else {
        $statusMsg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
        Error deleting status.
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
            <span aria-hidden='true'>&times;</span>
        </button>
    </div>";
        
    }
}

$statusData = [];
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['Status_ID'])) {
    $statusId = $_GET['Status_ID'];
    $statusData = editStatus($conn, $statusId);
}

// Fetch all statuses
$statusQuery = "SELECT * FROM status_table";
$statusResult = mysqli_query($conn, $statusQuery);
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
  <link href="img/logo/qc.png" rel="icon">
  <title>Manage Status</title>
 
</head>
<body id="page-top">
  <div id="wrapper">
    <?php include "Includes/sidebar.php"; ?>
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <?php include "Includes/topbar.php"; ?>
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Status Management</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="./">Home</a></li>
                <li class="breadcrumb-item active">Status Management</li>
            </ol>
          </div>
          <?php if (!empty($statusMsg)) echo $statusMsg; ?>
          <div class="row">
            <div class="col-lg-4">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                  <h5 class="m-0 font-weight-bold text-primary">Status Form</h5>
                  
                </div>

                <div class="card-body">
                  <form method="post">
                    <input type="hidden" name="status_id" value="<?php echo $statusData['Status_ID'] ?? ''; ?>">
                    <div class="form-group">
                      <label for="statusName">Status Name</label>
                      <input type="text" class="form-control" id="statusName" name="status_name" required value="<?php echo $statusData['Status_Name'] ?? ''; ?>">
                    </div>
                    <button type="submit" name="submit_status" class="btn btn-primary">Submit</button>
                  </form>
                </div>
              </div>
            </div>

            <div class="col-lg-8">
              <div class="card mb-4">
              <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h5 class="m-0 font-weight-bold text-primary">Status List</h6>
              </div>
              <div class="card-body">
                  <div class="table-responsive">
                      <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                          <thead class="thead-light">
                          <tr>
                       
                       <th>Status Name</th>
                       <th>Actions</th>
                     </tr>
                   </thead>
                   <tbody>
                     <?php while ($row = mysqli_fetch_assoc($statusResult)): ?>
                       <tr>
                         
                         <td><?php echo $row['Status_Name']; ?></td>
                         <td>
                           <a href="?action=edit&Status_ID=<?php echo $row['Status_ID']; ?>" class='btn btn-info btn-sm'>
                               <i class='fas fa-edit'></i>
                           </a>
                           <a href="?action=delete&Status_ID=<?php echo $row['Status_ID']; ?>" class='btn btn-danger btn-sm' onclick="return confirm('Are you sure you want to delete this status?');">
                               <i class='fas fa-trash'></i>
                           </a>
                         </td>
                       </tr>
                     <?php endwhile; ?>
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
