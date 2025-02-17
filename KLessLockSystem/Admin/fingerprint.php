<?php 
error_reporting(E_ALL);
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Function to create a fingerprint entry
function createFingerprint($conn) {
    $user_id = $_POST['user_id'];
    $fingerprint_id = $_POST['fingerprint_id'];

    // Check for duplicate Fingerprint_ID
    $checkQuery = "SELECT * FROM fingerprint_table WHERE Fingerprint_ID = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $fingerprint_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return false; // Duplicate Fingerprint_ID
    }

    $query = "INSERT INTO fingerprint_table (Fingerprint_ID, User_ID) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $fingerprint_id, $user_id);
    
    return $stmt->execute();
}

// Function to delete a fingerprint entry
function deleteFingerprint($conn, $fingerprintId) {
    $query = "DELETE FROM fingerprint_table WHERE Fingerprint_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $fingerprintId);
    return $stmt->execute();
}

// Function to edit fingerprint data
function editFingerprint($conn, $fingerprintId) {
    if ($stmt = $conn->prepare("SELECT * FROM fingerprint_table WHERE Fingerprint_ID=?")) {
        $stmt->bind_param("i", $fingerprintId);
        $stmt->execute();
        $result = $stmt->get_result();
        $fingerprintData = $result->fetch_assoc();
        $stmt->close();
        return $fingerprintData;
    }
    return null;
}

// Handle save, delete, and edit actions for fingerprints
$statusMsg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_fingerprint'])) {
        if (createFingerprint($conn)) {
            $statusMsg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
            Fingerprint Added Successfully!
                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                    <span aria-hidden='true'>&times;</span>
                </button>
            </div>";
        } else {
            $statusMsg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
            Error: Fingerprint ID is Taken.
                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                    <span aria-hidden='true'>&times;</span>
                </button>
            </div>";
        }
    } elseif (isset($_POST['edit_fingerprint'])) {
        $fingerprint_id = $_POST['fingerprint_id'];
        $user_id = $_POST['user_id'];

        // Update logic remains the same
        $query = "UPDATE fingerprint_table SET User_ID=? WHERE Fingerprint_ID=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $user_id, $fingerprint_id);
        
        if ($stmt->execute()) {
            $statusMsg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
            Fingerprint Updated Successfully!
                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                    <span aria-hidden='true'>&times;</span>
                </button>
            </div>";
        } else {
            $statusMsg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
            Error updating fingerprint: " . $stmt->error . "
                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                    <span aria-hidden='true'>&times;</span>
                </button>
            </div>";
        }
        $stmt->close();
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['Fingerprint_ID'])) {
    $fingerprintId = $_GET['Fingerprint_ID'];
    if (deleteFingerprint($conn, $fingerprintId)) {
        $statusMsg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
            Fingerprint Deleted Successfully!
                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                    <span aria-hidden='true'>&times;</span>
                </button>
            </div>";
        
    } else {
        $statusMsg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
        Error deleting fingerprint.
            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                <span aria-hidden='true'>&times;</span>
            </button>
        </div>";
    }
}

$fingerprintData = [];
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['Fingerprint_ID'])) {
    $fingerprintId = $_GET['Fingerprint_ID'];
    $fingerprintData = editFingerprint($conn, $fingerprintId);
}

// Fetch fingerprints along with user details (first_name, last_name)
$fingerprintQuery = "SELECT f.Fingerprint_ID, f.User_ID, u.first_name, u.last_name 
                     FROM fingerprint_table f
                     JOIN user_table u ON f.User_ID = u.User_ID
                     ORDER BY f.Fingerprint_ID DESC"; // Assuming 'User_ID' is the linking column

$fingerprintResult = mysqli_query($conn, $fingerprintQuery);
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
  <title>Manage Fingerprint</title>
  
</head>
<body id="page-top">
  <div id="wrapper">
    <?php include "Includes/sidebar.php"; ?>
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <?php include "Includes/topbar.php"; ?>
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Fingerprint Management</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="./">Home</a></li>
                <li class="breadcrumb-item active">Fingerprint Management</li>
            </ol>
          </div>
<?php if (!empty($statusMsg)) echo $statusMsg; ?>
          <div class="row">
            <div class="col-lg-4">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                  <h5 class="m-0 font-weight-bold text-primary">Fingerprint Form</h5>
                  
                </div>

                <div class="card-body">
                  <form method="post">
                    <input type="hidden" name="fingerprint_id" value="<?php echo $fingerprintData['Fingerprint_ID'] ?? ''; ?>">
                    <div class="form-group">
                      <label for="fingerprintId">Fingerprint ID</label>
                      <input type="number" class="form-control" id="fingerprintId" name="fingerprint_id" required value="<?php echo $fingerprintData['Fingerprint_ID'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                      <label for="userId">User ID</label>
                      <input type="number" class="form-control" id="userId" name="user_id" required value="<?php echo $fingerprintData['User_ID'] ?? ''; ?>">
                    </div>
                    <button type="submit" name="submit_fingerprint" class="btn btn-primary">Submit</button>
                  </form>
                </div>
              </div>
            </div>

            <div class="col-lg-8">
              <div class="card mb-4">
                                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                        <h5 class="m-0 font-weight-bold text-primary">Fingerprint List</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                                                <thead class="thead-light">
                                                <tr>
                        <th>Fingerprint ID</th>
                        <th>User Name</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($row = mysqli_fetch_assoc($fingerprintResult)): ?>
                      <tr>
                        <td><?php echo $row['Fingerprint_ID']; ?></td>
                        <td><?php echo ucwords(strtolower($row['first_name'])) . ' ' . ucwords(strtolower($row['last_name'])); ?></td> <!-- Capitalize first and last name -->

                        <td>
                            <a href="?action=edit&Fingerprint_ID=<?php echo $row['Fingerprint_ID']; ?>" class='btn btn-info btn-sm'>
                                <i class='fas fa-edit'></i>
                            </a>
                            <a href="?action=delete&Fingerprint_ID=<?php echo $row['Fingerprint_ID']; ?>" class='btn btn-danger btn-sm' onclick="return confirm('Are you sure you want to delete this fingerprint?');">
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
