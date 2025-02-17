<?php 
error_reporting(E_ALL);
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Function to create an iPad entry
function createiPad($conn) {
    $serial_num = $_POST['serial_num'];
    $model = $_POST['model'];
    $status_id = $_POST['status_id'];

    // Check for duplicate Serial Number
    $checkQuery = "SELECT * FROM ipad_table WHERE Serial_num = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $serial_num);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return false; // Duplicate Serial Number
    }

    $query = "INSERT INTO ipad_table (Serial_num, Model, Status_ID) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $serial_num, $model, $status_id);
    
    return $stmt->execute();
}

// Function to delete an iPad entry
function deleteiPad($conn, $ipadId) {
    $query = "DELETE FROM ipad_table WHERE iPad_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $ipadId);
    return $stmt->execute();
}

// Function to edit iPad data
function editiPad($conn, $ipadId) {
    $stmt = $conn->prepare("SELECT * FROM ipad_table WHERE iPad_ID=?");
    $stmt->bind_param("i", $ipadId);
    $stmt->execute();
    $result = $stmt->get_result();
    $ipadData = $result->fetch_assoc();
    $stmt->close();
    return $ipadData;
}

// Handle save, delete, and edit actions for iPads
$statusMsg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_ipad'])) {
        if (createiPad($conn)) {
            $statusMsg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
           iPad Added Successfully!
            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                <span aria-hidden='true'>&times;</span>
            </button>
        </div>";
        } else {
          
            $statusMsg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
            Error: Duplicate Serial Number.
             <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                 <span aria-hidden='true'>&times;</span>
             </button>
         </div>";
        }
    } elseif (isset($_POST['edit_ipad'])) {
        $ipad_id = $_POST['ipad_id'];
        $serial_num = $_POST['serial_num'];
        $model = $_POST['model'];
        $status_id = $_POST['status_id'];

        // First, fetch the current serial number for this iPad
        $currentQuery = "SELECT Serial_num FROM ipad_table WHERE iPad_ID = ?";
        $stmt = $conn->prepare($currentQuery);
        $stmt->bind_param("i", $ipad_id);
        $stmt->execute();
        $stmt->bind_result($current_serial_num);
        $stmt->fetch();
        $stmt->close();

        // Check for duplicate Serial Number, ignoring the current iPad ID
        if ($serial_num !== $current_serial_num) {
            $checkQuery = "SELECT * FROM ipad_table WHERE Serial_num = ? AND iPad_ID != ?";
            $stmt = $conn->prepare($checkQuery);
            $stmt->bind_param("si", $serial_num, $ipad_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $statusMsg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                Error: Duplicate Serial Number.
                 <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                     <span aria-hidden='true'>&times;</span>
                 </button>
             </div>";
            } else {
                // Perform the update as the serial number is unique
                $query = "UPDATE ipad_table SET Serial_num=?, Model=?, Status_ID=? WHERE iPad_ID=?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssii", $serial_num, $model, $status_id, $ipad_id);
                
                if ($stmt->execute()) {
                    $statusMsg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                    iPad Updated Successfully!
                    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                    </button>
                </div>";
                } else {
                    $statusMsg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    Error updating iPad: " . $stmt->error . "
                     <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                         <span aria-hidden='true'>&times;</span>
                     </button>
                 </div>";
                }
                $stmt->close();
            }
        } else {
            // If the serial number hasn't changed, update directly without checking for duplicates
            $query = "UPDATE ipad_table SET Model=?, Status_ID=? WHERE iPad_ID=?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sii", $model, $status_id, $ipad_id);
            
            if ($stmt->execute()) {
                $statusMsg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                iPad Updated Successfully!
                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                    <span aria-hidden='true'>&times;</span>
                </button>
            </div>";
            } else {
                $statusMsg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    Error updating iPad: " . $stmt->error . "
                     <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                         <span aria-hidden='true'>&times;</span>
                     </button>
                 </div>";
            }
            $stmt->close();
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['iPad_ID'])) {
    $ipadId = $_GET['iPad_ID'];
    if (deleteiPad($conn, $ipadId)) {
        $statusMsg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
        iPad Deleted Successfully!
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
            <span aria-hidden='true'>&times;</span>
        </button>
    </div>";
    } else {
        $statusMsg = "<div class='alert alert-danger'>Error deleting iPad.</div>";
        $statusMsg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
        Error deleting iPad.
         <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
             <span aria-hidden='true'>&times;</span>
         </button>
     </div>";
    }
}

$ipadData = [];
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['iPad_ID'])) {
    $ipadId = $_GET['iPad_ID'];
    $ipadData = editiPad($conn, $ipadId);
}

// Fetch all iPads
$ipadQuery = "SELECT ipad_table.*, status_table.Status_Name FROM ipad_table JOIN status_table ON ipad_table.Status_ID = status_table.Status_ID";
$ipadResult = mysqli_query($conn, $ipadQuery);
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
            <h1 class="h3 mb-0 text-gray-800">iPad Management</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="./">Home</a></li>
                <li class="breadcrumb-item active">iPad Management</li>
            </ol>
          </div>
          <?php if (!empty($statusMsg)) echo $statusMsg; ?>

          <div class="row">
            <div class="col-lg-4">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                  <h5 class="m-0 font-weight-bold text-primary">Add iPad </h5>
                </div>

                <div class="card-body">
                <form method="post">
                    <input type="hidden" name="ipad_id" value="<?php echo $ipadData['iPad_ID'] ?? ''; ?>">
                    <div class="form-group">
                      <label for="serialNum">Serial Number</label>
                      <input type="text" class="form-control" id="serialNum" name="serial_num" required value="<?php echo $ipadData['Serial_num'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                      <label for="model">Model</label>
                      <input type="text" class="form-control" id="model" name="model" required value="<?php echo $ipadData['Model'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                      <label for="statusSelect">Status</label>
                      <select class="form-control" id="statusSelect" name="status_id" required>
                        <?php
                        // Fetch all statuses for the dropdown
                        $statusQuery = "SELECT * FROM status_table";
                        $statusResult = mysqli_query($conn, $statusQuery);
                        while ($status = mysqli_fetch_assoc($statusResult)): ?>
                          <option value="<?php echo $status['Status_ID']; ?>" <?php echo (isset($ipadData['Status_ID']) && $ipadData['Status_ID'] == $status['Status_ID']) ? 'selected' : ''; ?>>
                            <?php echo $status['Status_Name']; ?>
                          </option>
                        <?php endwhile; ?>
                      </select>
                    </div>
                    <button type="submit" name="edit_ipad" class="btn btn-primary">Save Changes</button>
                    <button type="submit" name="submit_ipad" class="btn btn-success">Add iPad</button>
                  </form>
                </div>
              </div>
            </div>

            <div class="col-lg-8">
                                              <div class="card mb-4">
                                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                        <h5 class="m-0 font-weight-bold text-primary">iPad List</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                                                <thead class="thead-light">
                                                <tr>
                        <th>Serial Number</th>
                        <th>Model</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php while ($ipad = mysqli_fetch_assoc($ipadResult)): ?>
                        <tr>
                          <td><?php echo $ipad['Serial_num']; ?></td>
                          <td><?php echo $ipad['Model']; ?></td>
                          <td><?php echo $ipad['Status_Name']; ?></td>
                          <td>
                            <a href="?action=edit&iPad_ID=<?php echo $ipad['iPad_ID']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="?action=delete&iPad_ID=<?php echo $ipad['iPad_ID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this iPad?');">Delete</a>
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
