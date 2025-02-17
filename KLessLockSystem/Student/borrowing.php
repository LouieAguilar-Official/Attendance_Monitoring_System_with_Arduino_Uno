<?php 
error_reporting(E_ALL); // Enable error reporting for debugging
include '../Includes/dbcon.php';
include '../Includes/session.php';
// Automatically fetch the user ID from the session


$userId = $_SESSION['userId'];

if (!$userId) {
    die("User ID not found in session.");
}

$message = ""; // Variable to hold the message
$hasBorrowed = false; // Track if the user has already borrowed an iPad
$hasReturn = false;
$hasPending = false;

// Check if the user has an active borrowing record
if ($userId) {
    // Check if the user has an active borrow record (i.e., has not yet returned the iPad)
    $checkBorrowQuery = "SELECT COUNT(*) AS count FROM borrow WHERE User_ID = ? AND Return_datetime IS NULL";
    $checkStmt = $conn->prepare($checkBorrowQuery);
    $checkStmt->bind_param("i", $userId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        $hasBorrowed = true;
    }
    
    // Check if there is a pending return request (iPad status is 'ReturnPending')
    $checkReturnQuery = "SELECT COUNT(*) AS count FROM borrow b JOIN ipad_table ipad ON b.iPad_ID = ipad.iPad_ID WHERE User_ID = ? AND ipad.Status_ID = (SELECT Status_ID FROM status_table WHERE Status_Name = 'ReturnPending')";
    $checkReturnStmt = $conn->prepare($checkReturnQuery);
    $checkReturnStmt->bind_param("i", $userId);
    $checkReturnStmt->execute();
    $returnResult = $checkReturnStmt->get_result();
    $returnRow = $returnResult->fetch_assoc();

    if ($returnRow['count'] > 0) {
        $hasReturn = true;
    }
}

// Handle form submission for borrowing iPads
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ipadId']) && !$hasBorrowed) {
    $ipadId = $_POST['ipadId'];
    $borrowDatetime = date('Y-m-d H:i:s');

    // Insert the borrowing record into the database
    $query = "INSERT INTO borrow (User_ID, iPad_ID, Borrow_datetime) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iis", $userId, $ipadId, $borrowDatetime);

    if ($stmt->execute()) {
        // Update the iPad status to Pending
        $updateStatusQuery = "UPDATE ipad_table SET Status_ID = (SELECT Status_ID FROM status_table WHERE Status_Name = 'Pending') WHERE iPad_ID = ?";
        $updateStatusStmt = $conn->prepare($updateStatusQuery);
        $updateStatusStmt->bind_param("i", $ipadId);
        $updateStatusStmt->execute();

        $message = "<div class='alert alert-success'>iPad borrowed successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Failed to borrow iPad: " . $stmt->error . "</div>";
    }
} elseif ($hasBorrowed) {
    $message = "<div class='alert alert-danger'>You can only borrow one iPad at a time.</div>";
}


//////////////////return
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_pending']) && !$hasReturn) {
    $ipadId = $_POST['return_pending'];
    $returnDatetime = date('Y-m-d H:i:s');

    // Update the borrow record to reflect the return request
    $query = "UPDATE borrow SET Return_datetime = ? WHERE iPad_ID = ? AND User_ID = ? AND Return_datetime IS NULL";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sii", $returnDatetime, $ipadId, $userId);  // Bind params: datetime, ipadId, userId

    if ($stmt->execute()) {
        // Update the iPad status to "Pending" for return approval
        $updateStatusQuery = "UPDATE ipad_table SET Status_ID = (SELECT Status_ID FROM status_table WHERE Status_Name = 'ReturnPending') WHERE iPad_ID = ?";
        $updateStatusStmt = $conn->prepare($updateStatusQuery);
        $updateStatusStmt->bind_param("i", $ipadId);
        $updateStatusStmt->execute();

        $message = "<div class='alert alert-success'>Return request submitted successfully. iPad is now pending return approval.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Failed to submit return request: " . $stmt->error . "</div>";
    }

    // Close prepared statements
    $stmt->close();
    $updateStatusStmt->close();
} elseif ($hasReturn) {
    $message = "<div class='alert alert-danger'>You can only request one return at a time.</div>";
}








// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_pending'])) {
//     $ipadId = $_POST['return_pending'];

//     // Check if the user has already borrowed this iPad (so we only allow return on items borrowed)
//     $checkBorrowQuery = "SELECT Borrow_ID, User_ID FROM borrow WHERE iPad_ID = ? AND User_ID = ? AND Return_datetime IS NULL";
//     $checkBorrowStmt = $conn->prepare($checkBorrowQuery);

//     // Check if prepare() was successful
//     if ($checkBorrowStmt === false) {
//         die('MySQL prepare error: ' . $conn->error);
//     }

//     $checkBorrowStmt->bind_param("ii", $ipadId, $userId);
//     $checkBorrowStmt->execute();
//     $result = $checkBorrowStmt->get_result();

//     if ($result->num_rows > 0) {
//         // Update the iPad's status to "Return Pending"
//         $updateStatusQuery = "UPDATE ipad_table SET Status_ID = 11 WHERE iPad_ID = ?";
//         $updateStatusStmt = $conn->prepare($updateStatusQuery);

//         // Check if prepare() was successful for the update query
//         if ($updateStatusStmt === false) {
//             die('MySQL prepare error: ' . $conn->error);
//         }

//         $updateStatusStmt->bind_param("i", $ipadId);
//         if ($updateStatusStmt->execute()) {
//             // Optionally update the borrow record to reflect that a return is pending
//             $updateBorrowQuery = "UPDATE borrow SET Return_datetime = 0 WHERE iPad_ID = ? AND User_ID = ? AND Return_datetime IS NULL";
//             $updateBorrowStmt = $conn->prepare($updateBorrowQuery);

//             // Check if prepare() was successful for the borrow record update
//             if ($updateBorrowStmt === false) {
//                 die('MySQL prepare error: ' . $conn->error);
//             }

//             $updateBorrowStmt->bind_param("ii", $ipadId, $userId);
//             $updateBorrowStmt->execute();

//             $message = "<div class='alert alert-success'>Return request submitted successfully. iPad is now pending return approval.</div>";
//         } else {
//             $message = "<div class='alert alert-danger'>Failed to update iPad status to 'Return Pending'.</div>";
//         }
//     } else {
//         $message = "<div class='alert alert-danger'>You do not have this iPad borrowed, or it's already returned.</div>";
//     }

//     // Close prepared statements
//     $checkBorrowStmt->close();
//     $updateStatusStmt->close();
//     $updateBorrowStmt->close();
// }



// Handle cancellation of borrowed iPad
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow_id'])) {
    $borrowId = $_POST['borrow_id'];

    // Begin transaction
    mysqli_begin_transaction($conn);
    try {
        // Update the iPad status to Available
        $updateStatusQuery = "UPDATE ipad_table 
                               SET Status_ID = (SELECT Status_ID FROM status_table WHERE Status_Name = 'Returned') 
                               WHERE iPad_ID = (SELECT iPad_ID FROM borrow WHERE Borrow_ID = ? AND User_ID = ?)";
        $updateStatusStmt = $conn->prepare($updateStatusQuery);
        $updateStatusStmt->bind_param("ii", $borrowId, $userId);
        $updateStatusStmt->execute();

        // Delete the borrowing record
        $deleteBorrowQuery = "DELETE FROM borrow WHERE Borrow_ID = ? AND User_ID = ?";
        $deleteBorrowStmt = $conn->prepare($deleteBorrowQuery);
        $deleteBorrowStmt->bind_param("ii", $borrowId, $userId);
        $deleteBorrowStmt->execute();

        // Commit the transaction
        mysqli_commit($conn);
        $message = "<div class='alert alert-success'>Borrowing canceled successfully!</div>";
    } catch (Exception $e) {
        // Rollback the transaction if something failed
        mysqli_rollback($conn);
        $message = "<div class='alert alert-danger'>Error canceling borrowing: " . $e->getMessage() . "</div>";
    }

    // Close prepared statements
    $updateStatusStmt->close();
    $deleteBorrowStmt->close();
}
// Handle return action to change the iPad status to 'ReturnPending' (ID 11)

// Handle the Return action (set iPad status to 'ReturnPending' or 11)
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_pending'])) {
//     $serialNum = $_POST['return_pending'];

//     // Begin a transaction
//     mysqli_begin_transaction($conn);
//     try {
//         // Update the iPad status to 'ReturnPending' (Status_ID = 11)
//         $updateStatusQuery = "UPDATE ipad_table 
//                                SET Status_ID = 11  -- Assuming 'ReturnPending' status ID is 11
//                                WHERE Serial_num = ?";
//         $updateStatusStmt = $conn->prepare($updateStatusQuery);
//         $updateStatusStmt->bind_param("s", $serialNum);
//         $updateStatusStmt->execute();

//         // Optionally update the borrow record (set Return_Approved = 0 for pending)
//         $updateBorrowQuery = "UPDATE borrow 
//                                SET Return_Approved = 0  -- 0 means not yet approved
//                                WHERE iPad_ID = (SELECT iPad_ID FROM ipad_table WHERE Serial_num = ?) 
//                                AND Return_datetime IS NULL"; 
//         $updateBorrowStmt = $conn->prepare($updateBorrowQuery);
//         $updateBorrowStmt->bind_param("s", $serialNum);
//         $updateBorrowStmt->execute();

//         // Commit the transaction
//         mysqli_commit($conn);
//         $message = "<div class='alert alert-success'>Return request submitted and pending approval.</div>";
//     } catch (Exception $e) {
//         // Rollback the transaction if something failed
//         mysqli_rollback($conn);
//         $message = "<div class='alert alert-danger'>Error processing return: " . $e->getMessage() . "</div>";
//     }

//     // Close prepared statements
//     $updateStatusStmt->close();
//     $updateBorrowStmt->close();
// }




// Handle deletion of disapproved borrow records
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_borrow_id'])) {
    $borrowIdToDelete = $_POST['delete_borrow_id'];

    // Delete the borrowing record and reset the iPad status to Available
    mysqli_begin_transaction($conn);
    try {
        // Update iPad status to "Available"
        $updateStatusQuery = "UPDATE ipad_table 
                               SET Status_ID = (SELECT Status_ID FROM status_table WHERE Status_Name = 'Available') 
                               WHERE iPad_ID = (SELECT iPad_ID FROM borrow WHERE Borrow_ID = ?)";
        $updateStatusStmt = $conn->prepare($updateStatusQuery);
        $updateStatusStmt->bind_param("i", $borrowIdToDelete);
        $updateStatusStmt->execute();

        // Delete the borrow record
        $deleteBorrowQuery = "DELETE FROM borrow WHERE Borrow_ID = ?";
        $deleteBorrowStmt = $conn->prepare($deleteBorrowQuery);
        $deleteBorrowStmt->bind_param("i", $borrowIdToDelete);
        $deleteBorrowStmt->execute();

        // Commit the transaction
        mysqli_commit($conn);
        $message = "<div class='alert alert-success'>Borrow record deleted successfully!</div>";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $message = "<div class='alert alert-danger'>Error deleting borrow record: " . $e->getMessage() . "</div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['serial_num'])) {
    $serialNum = $_POST['serial_num'];

    // Begin a transaction
    mysqli_begin_transaction($conn);
    try {
        // Update the iPad status to "Available"
        $updateStatusQuery = "UPDATE ipad_table 
                               SET Status_ID = (SELECT Status_ID FROM status_table WHERE Status_Name = 'Available') 
                               WHERE Serial_num = ?";
        $updateStatusStmt = $conn->prepare($updateStatusQuery);
        $updateStatusStmt->bind_param("s", $serialNum);
        $updateStatusStmt->execute();

        // Update the borrow record with the return datetime
        $updateBorrowQuery = "UPDATE borrow 
                               SET Return_datetime = NOW() 
                               WHERE iPad_ID = (SELECT iPad_ID FROM ipad_table WHERE Serial_num = ?) 
                               AND Return_datetime IS NULL";
        $updateBorrowStmt = $conn->prepare($updateBorrowQuery);
        $updateBorrowStmt->bind_param("s", $serialNum);
        $updateBorrowStmt->execute();

        // Commit the transaction
        mysqli_commit($conn);
        $message = "<div class='alert alert-success'>iPad returned successfully!</div>";
    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        mysqli_rollback($conn);
        $message = "<div class='alert alert-danger'>Error returning iPad: " . $e->getMessage() . "</div>";
    }

    // Close prepared statements
    $updateStatusStmt->close();
    $updateBorrowStmt->close();
}

// Fetch all available iPads
$availableiPadsQuery = "SELECT ipad_table.* 
FROM ipad_table
LEFT JOIN status_table ON ipad_table.Status_ID = status_table.Status_ID
WHERE status_table.Status_Name IN ('Returned', 'Available') 
   OR status_table.Status_Name IS NULL;
";
$availableiPadsResult = mysqli_query($conn, $availableiPadsQuery);

// Fetch all borrowed iPads
// Fetch all borrowed iPads
$borrowediPadsQuery = "
    SELECT DISTINCT b.Borrow_ID, b.User_ID, b.iPad_ID, b.Borrow_datetime, 
                    ipad.Serial_num, status.Status_Name, 
                    u.First_Name, u.Last_Name
    FROM borrow b
    JOIN ipad_table ipad ON b.iPad_ID = ipad.iPad_ID
    JOIN status_table status ON ipad.Status_ID = status.Status_ID
    JOIN user_table u ON b.User_ID = u.User_ID
    WHERE (b.Return_datetime IS NULL OR ipad.Status_ID = 
           (SELECT Status_ID FROM status_table WHERE Status_Name = 'ReturnPending'))
      AND b.User_ID = ?"; // Show only borrowed iPads for the logged-in user

$stmt = $conn->prepare($borrowediPadsQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$borrowediPadsResult = $stmt->get_result();
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
    <title>Borrowing</title>
  
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include "Includes/sidebar.php"; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include "Includes/topbar.php"; ?>

                <!-- Display the message here -->
                <div class="container-fluid" id="container-wrapper">
                    <?php if ($message): ?>
                        <div class="alert-container">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Borrowing</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="./">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Borrowing</li>
                        </ol>
                    </div>

                    <div class="row">
                        <!-- Available iPads -->
                        <div class="col-lg-4">
                            <div class="card mb-4">
                                <div class="card-header py-3">
                                    <h5 class="m-0 font-weight-bold text-primary">Available iPads</h5>
                                </div>
                                <div class="table-responsive p-3">
                                    <table class="table align-items-center table-flush table-hover" >
                                        <thead class="thead-light">
                                            <tr>
                                                <!-- <th>iPad ID</th> -->
                                                <th>Serial Number</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
    <?php while ($row = mysqli_fetch_assoc($availableiPadsResult)): ?>
        <tr>
            <!-- <td><?php echo htmlspecialchars($row['iPad_ID']); ?></td> -->
            <td><?php echo htmlspecialchars($row['Serial_num']); ?></td>
            <td>Available</td>
            <td>
                <?php if ($hasBorrowed || $hasReturn): ?>
                    <button class="btn btn-secondary btn-sm" disabled>
                        <i class='fas fa-plus'></i> Borrow
                    </button>
                <?php else: ?>
                    <form id="borrowForm-<?php echo $row['iPad_ID']; ?>" action="borrowing.php" method="post" style="display:inline;">
                        <input type="hidden" name="ipadId" value="<?php echo htmlspecialchars($row['iPad_ID']); ?>">
                        <button type="button" class="btn btn-info btn-sm" onclick="showTermsModal('<?php echo $row['iPad_ID']; ?>')">
                            <i class='fas fa-plus'></i> Borrow
                        </button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
</tbody>

<!-- Terms and Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" role="dialog" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    By borrowing this iPad, you agree to the following terms and conditions:
                </p>
                <ul>
                    <li>You are responsible for the care and safe return of the borrowed iPad.</li>
                    <li>If the iPad is lost or damaged while in your possession, you will be held financially responsible for the repair or replacement cost.</li>
                    <li>The iPad must be returned on or before the end of the day</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmTermsBtn">I Agree</button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentFormId = null;

    function showTermsModal(formId) {
        currentFormId = formId; // Store the form ID
        $('#termsModal').modal('show'); // Show the modal
    }

    document.getElementById('confirmTermsBtn').addEventListener('click', function () {
        if (currentFormId) {
            document.getElementById('borrowForm-' + currentFormId).submit(); // Submit the stored form
        }
    });
</script>

                                    </table>
                                </div>
                            </div>
                        </div>

           
<!-- Display the borrowed iPads -->
<div class="col-lg-8">
                            <div class="card mb-4">
                                <div class="card-header py-3">
                                <h5 class="m-0 font-weight-bold text-primary">Borrowed Status</h5>
                                </div>
                                <div class="card-body">
                                    
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="dataTableHover">
                                            <thead class="thead-light">
                                            <tr>
                                                <th>Serial Number</th>
                                                <th>Borrow Date</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = mysqli_fetch_assoc($borrowediPadsResult)): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['Serial_num']); ?></td>
                                                    <td>
    <?php
    $borrowDatetime = strtotime($row['Borrow_datetime']);
    if ($borrowDatetime !== false) {
        echo date("F j, Y, g:i A", $borrowDatetime); // Formatted date and time
    } else {
        echo "Invalid Date/Time"; // Error handling
    }
    ?>
</td>
                                                    <td>
                                                        <?php echo htmlspecialchars($row['Status_Name']); ?>
                                                    </td>
                                                    <td>
                                                    <?php if ($row['Status_Name'] === 'Disapproved'): ?>
                                                        <form action="borrowing.php" method="post" style="display:inline;">
                                                            <input type="hidden" name="delete_borrow_id" value="<?php echo htmlspecialchars($row['Borrow_ID']); ?>">
                                                            <button type="submit" class="btn btn-danger btn-sm">Return</button>
                                                        </form>
                                                    <?php elseif ($row['Status_Name'] === 'Pending'): ?>
                                                        <form action="borrowing.php" method="post" style="display:inline;">
                                                            <input type="hidden" name="borrow_id" value="<?php echo htmlspecialchars($row['Borrow_ID']); ?>">
                                                            <button type="submit" class="btn btn-warning btn-sm">Cancel</button>
                                                        </form>
                                                        <?php elseif ($row['Status_Name'] === 'Approved'): ?>
                                                            <form action="borrowing.php" method="post" style="display:inline;">
                                                    <input type="hidden" name="return_pending" value="<?php echo htmlspecialchars($row['iPad_ID']); ?>">
                                                    <?php 
                                                    // Fetch the borrower's details
                                                    $userQuery = "
                                                        SELECT u.First_Name, u.Last_Name 
                                                        FROM user_table u 
                                                        JOIN borrow b ON u.User_ID = b.User_ID 
                                                        WHERE b.iPad_ID = '{$row['iPad_ID']}' 
                                                        LIMIT 1
                                                    ";
                                                    $userResult = mysqli_query($conn, $userQuery);

                                                    if ($userResult && mysqli_num_rows($userResult) > 0) {
                                                        $user = mysqli_fetch_assoc($userResult);
                                                        $firstName = htmlspecialchars($user['First_Name']);
                                                        $lastName = htmlspecialchars($user['Last_Name']);
                                                        echo "<span class='text-warning'>Please Claim Your iPad, ($firstName $lastName)</span>";
                                                    } else {
                                                        echo "<span class='text-warning'>Please Claim Your iPad</span>";
                                                    }
                                                    ?>
                                                </form>

                                                        <?php elseif ($row['Status_Name'] === 'Returned'): ?>
                                                        <form action="borrowing.php" method="post" style="display:inline;">
                                                            <input type="hidden" name="delete_borrow_id" value="<?php echo htmlspecialchars($row['Borrow_ID']); ?>">
                                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                    <?php endif; ?>

                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>

                                        </table>
                                </div>
                            </div>
                        </div>
                        <!-- end borrowed ipads -->

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
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#dataTableHover').DataTable(); // ID From dataTable with Hover
        });
    </script>
</body>
</html>
