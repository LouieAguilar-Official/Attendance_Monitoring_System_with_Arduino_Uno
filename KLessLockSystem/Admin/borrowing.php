
<?php
error_reporting(E_ALL);
include '../Includes/dbcon.php'; // Database connection
include '../Includes/session.php'; // Session handling for admin

// Query to fetch borrowed iPads with statuses 3 (Available), 4 (Approved), 8 (Pending), and 9 (Returned)
$query = "
    SELECT b.Borrow_ID, u.School_ID, b.iPad_ID, b.Borrow_datetime, u.First_name, u. Last_name,  
           i.Serial_num, i.Status_ID
    FROM borrow b
    JOIN ipad_table i ON b.iPad_ID = i.iPad_ID
    JOIN user_table u ON b.User_ID = u.User_ID
    WHERE i.Status_ID IN (3, 4, 8, 9)
";
$result = mysqli_query($conn, $query);

// Handle actions
if (isset($_GET['action']) && isset($_GET['borrow_id'])) {
    $borrow_id = intval($_GET['borrow_id']); // Sanitize input to avoid SQL injection
    $action = $_GET['action'];
    $new_status_id = null;

    switch ($action) {
        case 'approve':
            $new_status_id = 4;
            break;
        case 'disapprove':
            $new_status_id = 5;
            break;
        case 'return':
            $new_status_id = 9;
            break;
    }

    if ($new_status_id !== null) {
        // Update the status of the iPad
        $update_query = "
            UPDATE ipad_table 
            SET Status_ID = $new_status_id 
            WHERE iPad_ID = (SELECT iPad_ID FROM borrow WHERE Borrow_ID = $borrow_id)
        ";
        if (mysqli_query($conn, $update_query)) {
            // Update the borrow table status
            $borrow_update_query = "
                UPDATE borrow 
                SET Status_ID = $new_status_id 
                WHERE Borrow_ID = $borrow_id
            ";
           
        } else {
            echo "Error updating record: " . mysqli_error($conn);
        }
    }

    // Redirect to the same page to avoid resubmission
    header("Location: borrowing.php");
    exit();
}

// Function to get the status description
function getStatusDescription($status_id) {
    switch ($status_id) {
        case 3:
            return "Available";
        case 4:
            return "Approved";
        case 5:
            return "Disapproved";
        case 8:
            return "Pending";
        case 9:
            return "Returned";
        default:
            return "Unknown";
    }
}
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
  <title>Borrowing</title>
</head>

<body id="page-top">
  <div id="wrapper">
    <?php include "Includes/sidebar.php"; ?>
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <?php include "Includes/topbar.php"; ?>
        <div class="container-fluid" id="container-wrapper">
          <!-- Page Header -->
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Management Borrowing</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active">All Borrowing</li>
            </ol>
          </div>

          <!-- Scheduling Table -->
          <div class="col-lg-12">
            <!-- First Table: Scheduling Entries -->
                                          <div class="card mb-4">
                                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                        <h5 class="m-0 font-weight-bold text-primary">Borrowing Table</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                                                <thead class="thead-light">
                                                <tr>
                        <th>School ID</th>
                        <th>Student Name</th>
                        <th>Serial Number</th>
                        <th>Borrow Date</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                          <td><?php echo htmlspecialchars($row['School_ID']); ?></td>
                          <td><?php echo ucwords(htmlspecialchars($row['First_name'])) . " " . ucwords(htmlspecialchars($row['Last_name'])); ?></td>
                          <td><?php echo htmlspecialchars($row['Serial_num']); ?></td>
                          <td>
                            <?php
                            $borrowDatetime = strtotime($row['Borrow_datetime']);
                            if ($borrowDatetime !== false) {
                                echo date("F j, Y, g:i A", $borrowDatetime); // Format date and time
                            } else {
                                echo "Invalid Date/Time";
                            }
                            ?>
                          </td>
                          <td><?php echo getStatusDescription($row['Status_ID']); ?></td>
                          <td>
                            <?php if ($row['Status_ID'] == 8): ?> <!-- Pending -->
                                <a href="?action=approve&borrow_id=<?php echo $row['Borrow_ID']; ?>" 
                                   class="btn btn-success" 
                                   onclick="return confirmAction('approve this request?');">
                                   Approve
                                </a>
                                <a href="?action=disapprove&borrow_id=<?php echo $row['Borrow_ID']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirmAction('disapprove this request?');">
                                   Disapprove
                                </a>
                            <?php elseif ($row['Status_ID'] == 4): ?> <!-- Approved -->
                                <a href="?action=disapprove&borrow_id=<?php echo $row['Borrow_ID']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirmAction('disapprove this request?');">
                                   Disapprove
                                </a>
                                <a href="?action=return&borrow_id=<?php echo $row['Borrow_ID']; ?>" 
                                   class="btn btn-warning" 
                                   onclick="return confirmAction('mark this item as returned?');">
                                   Mark as Returned
                                </a>
                            <?php elseif ($row['Status_ID'] == 5): ?> <!-- Disapproved -->
                                <span class="text-muted">Disapproved</span>
                            <?php elseif ($row['Status_ID'] == 6): ?> <!-- Returned -->
                                <span class="text-success">Returned</span>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php } ?>
                    </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
          </div>
          
          <!-- Scroll to Top Button -->
          <a class="scroll-to-top rounded" href="#page-top">
            <i class="fas fa-angle-up"></i>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
  <script>
    $(document).ready(function () {
      // Initialize DataTables
      $('#dataTableHover').DataTable({
        "order": [[0, 'desc']] // Sorting by first column (Record_ID) in descending order
      });
    });

    function confirmAction(message) {
      return confirm(`Are you sure you want to ${message}`);
    }
  </script>
</body>

</html>
