<?php
// Error reporting
error_reporting(E_ALL);

// Includes
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Fetch User ID
$user_id = $_SESSION['userId'] ?? null;
if (!$user_id) {
    die("User ID not found in session.");
}

// Fetch the School ID for the logged-in user
$sql = "SELECT School_ID FROM user_table WHERE User_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id); // Bind the user_id
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $loggedInStudentSchoolId = $user['School_ID']; // Store the School_ID
} else {
    die("User not found in the database.");
}

// Helper Functions
function createSchedulingEntry($conn, $user_id, $start_time, $end_time, $date, $label, $school_ids, $status_id) {
  $query = "INSERT INTO scheduling_table (User_ID, Sched_time, End_time, Date, Label, Status_ID, List) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($query);

  // Convert the array to a JSON string before saving
  $school_ids_json = json_encode($school_ids);

  $stmt->bind_param("issssis", $user_id, $start_time, $end_time, $date, $label, $status_id, $school_ids_json);

  $result = $stmt->execute();
  $stmt->close();

  return $result;
}


function deleteSchedulingEntry($conn, $scheduleId) {
    $query = "DELETE FROM scheduling_table WHERE Schedule_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $scheduleId);
    return $stmt->execute();
}

function editSchedulingEntry($conn, $scheduleId) {
    $query = "SELECT * FROM scheduling_table WHERE Schedule_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $scheduleId);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    return $data;
}

// Function to check if a user already has an entry on the same date
function hasExistingEntry($conn, $user_id, $date, $schedule_id = null) {
    $query = "SELECT COUNT(*) FROM scheduling_table WHERE User_ID = ? AND Date = ?";
    
    // Exclude the current entry if editing
    if ($schedule_id) {
        $query .= " AND Schedule_ID != ?";
    }

    $stmt = $conn->prepare($query);
    
    if ($schedule_id) {
        $stmt->bind_param("isi", $user_id, $date, $schedule_id);
    } else {
        $stmt->bind_param("is", $user_id, $date);
    }

    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count > 0; // Returns true if the user already has an entry for the date
}

// Function to check if time is already taken
function isTimeTaken($conn, $start_time, $end_time, $date, $schedule_id = null) {
    $query = "SELECT COUNT(*) FROM scheduling_table WHERE 
              Date = ? AND (
                (? BETWEEN Sched_time AND End_time) OR (? BETWEEN Sched_time AND End_time) 
                OR (Sched_time BETWEEN ? AND ?) OR (End_time BETWEEN ? AND ?))";

    if ($schedule_id) {
        $query .= " AND Schedule_ID != ?";
    }

    $stmt = $conn->prepare($query);
    
    if ($schedule_id) {
        $stmt->bind_param("sssssssi", $date, $start_time, $end_time, $start_time, $end_time, $start_time, $end_time, $schedule_id);
    } else {
        $stmt->bind_param("sssssss", $date, $start_time, $end_time, $start_time, $end_time, $start_time, $end_time);
    }

    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count > 0; // Returns true if there is an overlap
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['submit'])) {
       // Retrieve form data
       $start_time = $_POST['sched_time'];
       $end_time = $_POST['end_time'];
       $date = $_POST['date'];
       $label = $_POST['label'];
       $school_ids_json = $_POST['list'];  // Get the JSON-encoded list
       $school_ids = json_decode($school_ids_json, true);  // Decode to an array
       $status_id = 8; // Default status

       // Validation checks
       if (hasExistingEntry($conn, $user_id, $date)) {
           $statusMsg = "<div class='alert alert-danger'>You already have a scheduling entry on this date. Only one entry is allowed per day.</div>";
       } elseif (isTimeTaken($conn, $start_time, $end_time, $date)) {
           $statusMsg = "<div class='alert alert-danger'>The selected time is already taken on the same date. Please choose a different time.</div>";
       } else {
           // Save the scheduling entry
           $statusMsg = createSchedulingEntry($conn, $user_id, $start_time, $end_time, $date, $label, $school_ids, $status_id)
               ? "<div class='alert alert-success'>Scheduling Entry Created Successfully!</div>"
               : "<div class='alert alert-danger'>Error creating scheduling entry.</div>";
       }
   }
   elseif (isset($_POST['edit'])) {
      // Editing an existing schedule
      $schedule_id = $_POST['scheduleId'];
      $start_time = $_POST['sched_time'];
      $end_time = $_POST['end_time'];
      $date = $_POST['date'];
      $school_ids_json = $_POST['list'];  // Get the list of school IDs as JSON
      $school_ids = json_decode($school_ids_json, true);  // Decode the JSON to an array

      // Check if the user already has a scheduling entry on the same date (excluding the current entry)
      if (hasExistingEntry($conn, $user_id, $date, $schedule_id)) {
          echo "<script>alert('You already have a scheduling entry on this date. Only one entry is allowed per day.');</script>";
      } 
      // Check if the time is already taken on the same date
      elseif (isTimeTaken($conn, $start_time, $end_time, $date, $schedule_id)) {
          echo "<script>alert('The selected time is already taken on the same date. Please choose a different time.');</script>";
      } else {
          // Proceed to update the scheduling entry with the list of school IDs
          $query = "UPDATE scheduling_table 
                    SET User_ID = ?, Sched_time = ?, End_time = ?, Date = ?, Label = ?, Status_ID = ?, List = ? 
                    WHERE Schedule_ID = ?";
          $stmt = $conn->prepare($query);
          $stmt->bind_param(
              "isssssii",
              $_POST['user_id'],
              $_POST['sched_time'],
              $_POST['end_time'],
              $_POST['date'],
              $_POST['label'],
              $_POST['status_id'],
              json_encode($school_ids),  // Convert the array back to JSON before saving
              $_POST['scheduleId']
          );
          $statusMsg = $stmt->execute()
              ? "<div class='alert alert-success'>Scheduling Entry Updated Successfully!</div>"
              : "<div class='alert alert-danger'>Error updating scheduling entry: " . $stmt->error . "</div>";
          $stmt->close();
      }
  }

  // Redirect with status message
  if (!isset($statusMsg)) {
      header("Location: scheduling.php?status=" . urlencode($statusMsg));
      exit;
  }
}






if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['Schedule_ID'])) {
    $statusMsg = deleteSchedulingEntry($conn, $_GET['Schedule_ID'])
        ? "<div class='alert alert-success'>Scheduling Entry Deleted Successfully!</div>"
        : "<div class='alert alert-danger'>Error deleting scheduling entry.</div>";
}

// Fetch scheduling data
$schedulingQueryPending = "
    SELECT 
        s.Schedule_ID, 
        s.User_ID, 
        s.Label, 
        s.Sched_time, 
        s.End_time, 
        s.Date, 
        s.List, 
        s.Status_ID, 
        u.School_ID, 
        st.Status_Name
    FROM 
        scheduling_table s
    LEFT JOIN 
        user_table u ON s.User_ID = u.User_ID
    LEFT JOIN 
        status_table st ON s.Status_ID = st.Status_ID
    WHERE 
        s.Status_ID = 8";
$schedulingResultPending = mysqli_query($conn, $schedulingQueryPending);

$currentDate = date('Y-m-d');
$schedulingQueryApprove = "
    SELECT 
        s.Schedule_ID, 
        s.User_ID, 
        s.Label, 
        s.Sched_time, 
        s.End_time, 
        s.Date, 
        s.List, 
        s.Status_ID, 
        u.School_ID, 
        u.First_name, 
        u.Last_name, 
        st.Status_Name
    FROM 
        scheduling_table s
    LEFT JOIN 
        user_table u ON s.User_ID = u.User_ID
    LEFT JOIN 
        status_table st ON s.Status_ID = st.Status_ID
    WHERE 
        (s.Status_ID = 4 OR s.Status_ID = 12 OR st.Status_Name IN ('approved', 'viewed')) 
        AND s.Date = ? 
    ORDER BY 
        s.Date ASC, s.Sched_time ASC";

$stmtApprove = $conn->prepare($schedulingQueryApprove);
$stmtApprove->bind_param("s", $currentDate);
$stmtApprove->execute();
$schedulingResultApprove = $stmtApprove->get_result();



$courses = mysqli_query($conn, "SELECT Course_ID, Course_Name FROM course_table");
$years = mysqli_query($conn, "SELECT Year_ID, Year_Name FROM year_table");
$sections = mysqli_query($conn, "SELECT Section_ID, Section_Name FROM section_table");


$schoolIdsQuery = "SELECT School_ID FROM user_table";
$schoolIdsResult = mysqli_query($conn, $schoolIdsQuery);
$validSchoolIds = [];

// Store all valid School IDs in an array
while ($row = mysqli_fetch_assoc($schoolIdsResult)) {
    $validSchoolIds[] = $row['School_ID'];
}



// Convert PHP array to JavaScript array
$validSchoolIdsJson = json_encode($validSchoolIds);
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
  <title>Scheduling</title>

</head>
<body id="page-top">
  <div id="wrapper">
    <?php include "Includes/sidebar.php"; ?>
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <?php include "Includes/topbar.php"; ?>
        <div class="container-fluid" id="container-wrapper">
        <?php if (!empty($statusMsg)) echo $statusMsg; ?>
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Scheduling for Laboratory</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active">Scheduling for Laboratory</li>
            </ol>
          </div>

          <div class="row">
          
            <!-- Scheduling Form -->
            <div class="col-lg-3">
            
  <div class="card mb-4"> 
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
   
      <h5 class="m-0 font-weight-bold text-primary">Scheduling Form</h5>
      
    </div>
    <div class="card-body">
   
    <form method="POST" onsubmit="return validateTerms()">
        <input type="hidden" name="scheduleId" value="<?php echo $schedulingData['Schedule_ID'] ?? ''; ?>">
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

        <div class="form-group">
          <label for="label">Class Description</label>
          <input type="text" class="form-control" id="label" name="label" required value="<?php echo $schedulingData['Label'] ?? ''; ?>">
        </div>






        <div class="form-group">
    <label for="list">List (School IDs of group members)</label>
    <div class="input-group">
        <input type="text" class="form-control" id="list" placeholder="Enter School ID">
        <div class="input-group-append">
            <button type="button" class="btn btn-primary" id="addSchoolIdBtn">Add School ID</button>
        </div>
    </div>
</div>

<div id="schoolIdsList">
    <!-- Display the list of School IDs here -->
</div>
<script>
    // Initialize an array to store School IDs
    let schoolIds = [];

    // Fetch valid School IDs and logged-in student School ID from PHP
    const validSchoolIds = <?php echo $validSchoolIdsJson; ?>;
    const loggedInStudentSchoolId = "<?php echo $loggedInStudentSchoolId ?? ''; ?>";

    // Function to update the displayed list of School IDs in a single column (3 rows)
    function updateListDisplay() {
        const listContainer = document.getElementById('schoolIdsList');
        listContainer.innerHTML = schoolIds.length > 0
            ? '<div class="row">' + chunkArray(schoolIds, 3).map((group) => `
                <div class="col-12">
                    <div class="d-flex">
                        ${group.map((id) => `
                            <div class="mr-3">
                                <span>${id}</span>
                                <i class="fas fa-times-circle" onclick="removeSchoolId('${id}')" style="cursor: pointer; color: red;"></i>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `).join('') + '</div>'
            : '<p>No School IDs added yet.</p>';

        // Update the hidden input field with the JSON string
        document.getElementById('schoolIdsHidden').value = JSON.stringify(schoolIds);
    }

    // Function to chunk the array into groups of size 3 (for 3 IDs per row)
    function chunkArray(array, size) {
        const result = [];
        for (let i = 0; i < array.length; i += size) {
            result.push(array.slice(i, i + size));
        }
        return result;
    }

    // Function to handle adding a School ID
    document.getElementById('addSchoolIdBtn').addEventListener('click', function () {
        const inputField = document.getElementById('list');
        const schoolId = inputField.value.trim();

        if (!schoolId) {
            alert('Please enter a valid School ID!');
            return;
        }

        if (schoolId === loggedInStudentSchoolId) {
            alert('You cannot add your own School ID!');
        } else if (!validSchoolIds.includes(schoolId)) {
            alert('The entered School ID is not valid!');
        } else if (schoolIds.includes(schoolId)) {
            alert('This School ID is already added!');
        } else {
            schoolIds.push(schoolId); // Add the School ID
            inputField.value = '';   // Clear the input field
            updateListDisplay();     // Update the list display
        }
    });

    // Function to remove a School ID by value
    function removeSchoolId(schoolId) {
        schoolIds = schoolIds.filter(id => id !== schoolId); // Remove the specific ID
        updateListDisplay(); // Update the list display
    }

    // Optional: Preload the list from PHP if available
    <?php if (!empty($schedulingData['List'])): ?>
        const initialIds = "<?php echo $schedulingData['List']; ?>"
            .split(',')
            .map(id => id.trim());
        schoolIds = [...initialIds]; // Populate with pre-existing IDs
        updateListDisplay();        // Update the list display
    <?php endif; ?>
</script>



      

        <div class="form-row align-items-end">
          <div class="form-group">
            <label for="startTime">Start Time</label>
            <input type="time" class="form-control" id="schedTime" name="sched_time" required min="08:00" max="20:00" value="<?php echo $labClassData['sched_time'] ?? ''; ?>" oninput="validateTimes()">
          </div>
          <div class="form-group">
            <label for="endTime">End Time</label>
            <input type="time" class="form-control" id="endTime" name="end_time" required min="08:00" max="20:00" value="<?php echo $labClassData['End_Time'] ?? ''; ?>" oninput="validateTimes()">
          </div>
        </div>

        <div class="form-group">
    <label for="scheduleDate">Date of Schedule</label>
    <input type="date" class="form-control" id="scheduleDate" name="date" required 
           value="<?php echo $schedulingData['Date'] ?? ''; ?>" oninput="validateScheduleDate()">
</div>

<script>
    // Set the minimum date for the input field to today's date
    window.onload = function() {
        const scheduleDateInput = document.getElementById('scheduleDate');
        const today = new Date();

        // Format the date to YYYY-MM-DD
        const formattedDate = today.toISOString().split('T')[0];
        
        // Set the min attribute of the input field to today's date
        scheduleDateInput.setAttribute('min', formattedDate);
    }

    // Optional: Additional validation in case the user manually changes the date
    function validateScheduleDate() {
        const scheduleDateInput = document.getElementById('scheduleDate');
        const selectedDate = new Date(scheduleDateInput.value);
        const currentDate = new Date();

        currentDate.setHours(0, 0, 0, 0); // Normalize the current date for comparison

        if (selectedDate < currentDate) {
            alert('The selected date cannot be in the past!');
            scheduleDateInput.value = ''; // Reset the input field if date is in the past
        }
    }
</script>


      
        <input type="hidden" name="list" id="schoolIdsHidden">


        <!-- Terms and Conditions Section -->
        <div class="form-group d-flex align-items-center">
          <input type="checkbox" id="termsCheckbox" name="terms" class="form-check-input" required style="margin-left: 0.5%;">
          <label class="form-check-label" for="termsCheckbox" style="margin-left: 5%;">
            I agree to the <a href="#" data-toggle="modal" data-target="#termsModal">Terms and Conditions</a>.
          </label>
        </div>

        <button type="submit" name="submit" class="btn btn-primary">Submit</button>
      </form>
    </div>
  </div>
</div>



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
        <p>By using this scheduling system, you agree to the following terms:</p>
        <ul>
          <li>The laboratory is to be used only for educational purposes and under supervision.</li>
          <li>Scheduling is subject to approval by the administration.</li>
          <li>You must adhere to the scheduled time and vacate the lab promptly at the end of your slot.</li>
          <li>Damages to equipment during your session will be your responsibility.</li>
          <li>Cancellations must be reported at least 24 hours in advance.</li>
        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
  function validateTerms() {
    const termsCheckbox = document.getElementById('termsCheckbox');
    if (!termsCheckbox.checked) {
      alert('You must agree to the Terms and Conditions before submitting.');
      return false;
    }
    return true;
  }
</script>


            <!-- Scheduling Entries Table -->
            <div class="col-lg-9">
              <div class="card mb-4">
                                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                                    <h5 class="m-0 font-weight-bold text-primary">Scheduling Request</h5>
                                </div>
                                <div class="card-body">
                                    
                                       
                                    
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="dataTableHover">
                                            <thead class="thead-light">
                                            <tr>
                                                <th>Class Date</th>
                                                <th>Student ID</th>
                                                <th>Class Description</th>
                                                <th>Class StartTime</th>
                                                <th>Class EndTime</th>
                                                <th>Student Group</th>
                                                <th>Attendance Status</th>
                                                <th> Manage Actions</th>
                                              </tr>
                                            </thead>
                                            <tbody>
                     
                                          <!-- Display Scheduling Entries -->
                                          <?php while ($row = mysqli_fetch_assoc($schedulingResultPending)): ?>
                                              <tr>
                                                  <td><?php echo htmlspecialchars(date("F j, Y", strtotime($row['Date']))); ?></td>
                                                  <td><?php echo htmlspecialchars($row['School_ID']); ?></td>
                                                  <td><?php echo htmlspecialchars(ucwords($row['Label'])); ?></td>
                                                  <td><?php echo htmlspecialchars(date("g:i A", strtotime($row['Sched_time']))); ?></td>
                                                  <td><?php echo htmlspecialchars(date("g:i A", strtotime($row['End_time']))); ?></td>
                                                  <td>
                                                      <?php 
                                                          // Convert the comma-separated string into an array
                                                          $listArray = explode(',', $row['List']);
                                                          
                                                          // Remove whitespace and format the array items
                                                          $listArray = array_map('trim', $listArray);

                                                          // Join the items with a line break, no brackets at the beginning or end
                                                          echo implode(",<br>", $listArray);
                                                      ?>
                                                  </td>



                                                  <td><?php echo htmlspecialchars($row['Status_Name']); ?></td>
                                                  <td>
                                                      <?php if ($row['User_ID'] == $user_id): ?>
                                                          <a href="?action=edit&Schedule_ID=<?php echo htmlspecialchars($row['Schedule_ID']); ?>" class="btn btn-info btn-sm">
                                                              <i class="fas fa-edit"></i>
                                                          </a>
                                                          <a href="?action=delete&Schedule_ID=<?php echo htmlspecialchars($row['Schedule_ID']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this scheduling entry?');">
                                                              <i class="fas fa-trash"></i>
                                                          </a>
                                                      <?php endif; ?>
                                                  </td>
                                              </tr>
                                          <?php endwhile; ?>
                                        </tbody>
                                        </table>
                                    </div>
                                </div>
                                </div>

              <!-- Today's Scheduling Table -->
              
              <div class="card mb-4">
              <div class="card-header py-3 d-flex align-items-center justify-content-between">
                                    <h5 class="m-0 font-weight-bold text-primary">Today's Schedule</h5>
                                </div>
                                <div class="card-body">
                                    
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="dataTableHover">
                                            <thead class="thead-light">
                                            <tr>
                      <th>Student Name</th>
                        <th>Class Description</th>
                        <th>Class Start Time</th>
                        <th>Class End Time</th>
                       
                        <th>Student Group</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($row = mysqli_fetch_assoc($schedulingResultApprove)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(ucwords($row['First_name']) . ' ' . ucwords($row['Last_name'])); ?></td> <!-- First and Last Name -->
                            <td><?php echo htmlspecialchars($row['Label']); ?></td>
                            <td><?php echo date("g:i A", strtotime($row['Sched_time'])); ?></td>
                            <td><?php echo date("g:i A", strtotime($row['End_time'])); ?></td>
                            <td>
                                <?php 
                                    // Convert the comma-separated string into an array
                                    $listArray = explode(',', $row['List']);
                                    // Remove whitespace and format the array items
                                    $listArray = array_map('trim', $listArray);
                                    // Join the items with a line break, no brackets at the beginning or end
                                    echo implode(",<br>", $listArray);
                                ?>
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

  <!-- Scroll to top -->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <script>
    function validateScheduleDate() {
      const scheduleDateInput = document.getElementById('scheduleDate');
      const selectedDate = new Date(scheduleDateInput.value);
      const dayOfWeek = selectedDate.getDay();

      if (dayOfWeek === 6 || dayOfWeek === 0) {
        alert("Scheduling cannot be done on Saturday or Sunday.");
        scheduleDateInput.setCustomValidity("Class cannot be scheduled on Saturday or Sunday.");
      } else {
        scheduleDateInput.setCustomValidity('');
      }
    }

    function validateTimes() {
      const startTimeInput = document.getElementById('schedTime');
      const endTimeInput = document.getElementById('endTime');
      const startTime = startTimeInput.value;
      const endTime = endTimeInput.value;

      if (startTime && endTime) {
        const startDateTime = new Date('1970-01-01T' + startTime + 'Z');
        const endDateTime = new Date('1970-01-01T' + endTime + 'Z');
        const timeDifference = (endDateTime - startDateTime) / 1000 / 60 / 60;

        if (endTime <= startTime) {
          endTimeInput.setCustomValidity('End Time must be later than Start Time.');
        } else if (timeDifference < 1) {
          endTimeInput.setCustomValidity('The class duration must be at least 1 hour.');
        } else if (timeDifference > 5) {
          endTimeInput.setCustomValidity('The class duration cannot exceed 5 hours.');
        } else {
          endTimeInput.setCustomValidity('');
        }
      }
    }
  </script>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
  <script>
    $(document).ready(function () {
      $('#dataTableHover').DataTable({
        "order": [[0, 'desc']]
      });
    });
  </script>
</body>
</html>
