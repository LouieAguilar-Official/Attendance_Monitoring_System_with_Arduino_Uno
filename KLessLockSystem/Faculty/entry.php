<?php 
error_reporting(E_ALL); // Enable all error reporting for debugging
include '../Includes/dbcon.php';
include '../Includes/session.php'; // Include session handling

// Automatically fetch the user ID from the session
$userId = $_SESSION['userId'] ?? null;

if (!$userId) {
    die("User ID not found in session.");
}

// Initialize an array to hold entry records
$entryRecords = [];

// Automatically fetch records for the current user from the record_table and insert them into entry_table when the button is clicked
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fetch_data'])) {
    // Fetch records from record_table
    $fetchAllQuery = "
        SELECT 
            fingerprint_table.User_ID, 
            record_table.Record_ID, 
            record_table.Time_and_Date
        FROM record_table
        INNER JOIN fingerprint_table ON record_table.Fingerprint_ID = fingerprint_table.Fingerprint_ID
        WHERE fingerprint_table.User_ID = ? 
    ";

    // Use prepared statements for security
    if ($stmtFetchAll = mysqli_prepare($conn, $fetchAllQuery)) {
        mysqli_stmt_bind_param($stmtFetchAll, 'i', $userId);
        mysqli_stmt_execute($stmtFetchAll);
        $fetchAllResult = mysqli_stmt_get_result($stmtFetchAll);

        if ($fetchAllResult && mysqli_num_rows($fetchAllResult) > 0) {
            while ($fetchRow = mysqli_fetch_assoc($fetchAllResult)) {
                $userID = $fetchRow['User_ID'];
                $recordID = $fetchRow['Record_ID'];
                $timeAndDate = $fetchRow['Time_and_Date'];

                // Insert into entry_table if not already exists
                $insertEntryQuery = "
                    INSERT INTO entry_table (User_ID, Record_ID, Date_time)
                    SELECT ?, ?, ? 
                    WHERE NOT EXISTS (
                        SELECT * FROM entry_table 
                        WHERE User_ID = ? 
                        AND Record_ID = ? 
                        AND Date_time = ? 
                    )
                ";

                if ($stmtInsertEntry = mysqli_prepare($conn, $insertEntryQuery)) {
                    mysqli_stmt_bind_param($stmtInsertEntry, 'iissis', $userID, $recordID, $timeAndDate, $userID, $recordID, $timeAndDate);
                    mysqli_stmt_execute($stmtInsertEntry);
                    mysqli_stmt_close($stmtInsertEntry);
                }
            }
        }
        mysqli_stmt_close($stmtFetchAll);
    }
}

// Fetch entry records along with School_ID for display
$fetchEntryQuery = "
    SELECT 
        entry_table.Entry_ID, 
        entry_table.User_ID, 
        entry_table.Record_ID, 
        entry_table.Date_time, 
        user_table.School_ID, 
        user_table.First_name, 
        user_table.Last_name
    FROM entry_table
    INNER JOIN user_table ON entry_table.User_ID = user_table.User_ID
    WHERE entry_table.User_ID = ? 
";

if ($stmtFetchEntry = mysqli_prepare($conn, $fetchEntryQuery)) {
    mysqli_stmt_bind_param($stmtFetchEntry, 'i', $userId);
    mysqli_stmt_execute($stmtFetchEntry);
    $entryResult = mysqli_stmt_get_result($stmtFetchEntry);

    // Check if we got a valid mysqli_result
    if ($entryResult && mysqli_num_rows($entryResult) > 0) {
        while ($entryRow = mysqli_fetch_assoc($entryResult)) {
            $entryRecords[] = $entryRow; // Store fetched records in the array
        }
    }
    mysqli_stmt_close($stmtFetchEntry);
} else {
    echo "Error preparing fetch entry query: " . mysqli_error($conn);
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
    <link href="img/logo/kl.png" rel="icon">
  <title>Entries</title>
  
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include "Includes/sidebar.php"; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include "Includes/topbar.php"; ?>
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Entry Table</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="./">Home</a></li>
                            <li class="breadcrumb-item active">Entry Table</li>
                        </ol>
                    </div>

                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                                    <h5 class="m-0 font-weight-bold text-primary">Entries </h5>
                                </div>
                                <div class="card-body">
                                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                                        <form method="POST" action="">
                                            <button type="submit" name="fetch_data" class="btn btn-primary">Fetch Data</button>
                                        </form>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="dataTableHover">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>School ID</th>
                                                    <th> Name</th>
                                                    <th>Date of Attendance</th>
                                                   
                                                  
                                                   
                                                    
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (count($entryRecords) > 0): ?>
                                                    <?php foreach ($entryRecords as $record): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($record['School_ID']); ?></td>
                                                            <!-- Display Student Name -->
                                                            <td><?php echo htmlspecialchars(ucwords(strtolower($record['First_name']))) . ' ' . htmlspecialchars(ucwords(strtolower($record['Last_name']))); ?></td>
                                                            <!-- Display Date and Time -->
                                                            <td><?php echo htmlspecialchars(date('F j, Y, g:i A', strtotime($record['Date_time']))); ?></td>
                                                            <!-- Display School ID -->
                                                            

                                                         
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center">No records found.</td>
                                                    </tr>
                                                <?php endif; ?>
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
