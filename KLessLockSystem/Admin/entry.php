<?php 
error_reporting(E_ALL); // Enable all error reporting for debugging
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Automatically fetch all records from the record_table and insert them into entry_table
$fetchAllQuery = "
    SELECT 
        fingerprint_table.User_ID, 
        user_table.School_ID, 
        record_table.Record_ID, 
        record_table.Time_and_Date
    FROM record_table
    INNER JOIN fingerprint_table ON record_table.Fingerprint_ID = fingerprint_table.Fingerprint_ID
    INNER JOIN user_table ON fingerprint_table.User_ID = user_table.User_ID
";

$fetchAllResult = mysqli_query($conn, $fetchAllQuery);

if ($fetchAllResult && mysqli_num_rows($fetchAllResult) > 0) {
    while ($fetchRow = mysqli_fetch_assoc($fetchAllResult)) {
        $userID = $fetchRow['User_ID'];
        $schoolID = $fetchRow['School_ID'];
        $recordID = $fetchRow['Record_ID'];
        $timeAndDate = $fetchRow['Time_and_Date'];

        // Check if the entry already exists in the entry_table
        $checkQuery = "
            SELECT * FROM entry_table 
            WHERE Record_ID = '$recordID'
        ";
        $checkResult = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($checkResult) == 0) {
            // Prepare and execute insert query into entry_table
            $insertQuery = "
                INSERT INTO entry_table (User_ID, Record_ID, School_ID, Date_time)
                VALUES ('$userID', '$recordID', '$schoolID', '$timeAndDate')
            ";

            if (!mysqli_query($conn, $insertQuery)) {
                echo "Error inserting data: " . mysqli_error($conn);
            }
        }
    }
}

// Fetch data from entry_table for display
$query = " 
    SELECT 
        entry_table.Entry_ID, 
        entry_table.Record_ID, 
        user_table.first_name, 
        user_table.last_name, 
        user_table.School_ID, 
        DATE_FORMAT(entry_table.Date_time, '%M %d, %Y %h:%i %p') AS formatted_date
    FROM entry_table
    INNER JOIN record_table ON entry_table.Record_ID = record_table.Record_ID
    INNER JOIN fingerprint_table ON record_table.Fingerprint_ID = fingerprint_table.Fingerprint_ID
    INNER JOIN user_table ON fingerprint_table.User_ID = user_table.User_ID
";


$result = mysqli_query($conn, $query);

// Debugging: Check if the query was successful
if (!$result) {
    die("Query Failed: " . mysqli_error($conn)); // Check for errors in the query execution
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
  <title>Entry</title>
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
            <h1 class="h3 mb-0 text-gray-800">Management Entry</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active">All Entry</li>
            </ol>
          </div>

          <!-- Scheduling Table -->
          <div class="col-lg-12">
            <!-- First Table: Scheduling Entries -->
        <div class="card mb-4">
                                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                        <h5 class="m-0 font-weight-bold text-primary">Entries</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                                                <thead class="thead-light">
                                                <tr>
                        <th>School ID</th>
                        <th>Student Name</th>  <!-- Changed from 'User ID' to 'User Name' -->
                        <th>Date/Time</th>
                    
                    </tr>

                    </thead>
                    <tbody>
                    <?php if ($result): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                            <td><?php echo htmlspecialchars($row['School_ID']); ?></td>
                            <td><?php echo htmlspecialchars(ucwords(strtolower($row['first_name']))) . ' ' . htmlspecialchars(ucwords(strtolower($row['last_name']))); ?></td>
                                
                                <td><?php echo htmlspecialchars($row['formatted_date']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No records found in the entry table.</td>
                        </tr>
                    <?php endif; ?>

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
  </script>
</body>

</html>
