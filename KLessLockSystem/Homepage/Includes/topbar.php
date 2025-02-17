<?php 
// // include '../Includes/dbcon.php'; // Make sure to include your database connection file
// // include '../Includes/session.php'; // Include session management

// // Prepare and execute the query to fetch user information
// $stmt = $conn->prepare("SELECT * FROM user_table WHERE User_ID = ?");
// $stmt->bind_param("i", $_SESSION['userId']); // Bind the user ID as an integer
// $stmt->execute();
// $rs = $stmt->get_result();
// $num = $rs->num_rows;

// if ($num > 0) {
//     $rows = $rs->fetch_assoc();
//     $fullName = $rows['First_name'] . " " . $rows['Last_name'];
// } else {
//     $fullName = "User not found"; // Handle case when user is not found
// }

// $stmt->close(); // Close the statement
?>

<nav class="navbar navbar-expand navbar-light bg-gradient-primary topbar mb-4 static-top">



      
  
  
        <div class="text-white big" style="margin-left:100px;"><b></b></div>
          <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown no-arrow">
             
              <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                aria-labelledby="searchDropdown">
                <form class="navbar-search">
                  <div class="input-group">
                    <input type="text" class="form-control bg-light border-1 small" placeholder="What do you want to look for?"
                      aria-label="Search" aria-describedby="basic-addon2" style="border-color: #3f51b5;">
                    <div class="input-group-append">
                      <button class="btn btn-primary" type="button">
                        <i class="fas fa-search fa-sm"></i>
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </li>
         
            <div class="topbar-divider d-none d-sm-block"></div>
            <li class="nav-item dropdown no-arrow">
              
  <a class="nav-link dropdown-toggle" href="../index.php" role="button" >
    <img class="img-profile rounded-circle" src="img/user-icn.png" style="max-width: 60px" alt="User Profile">
    <span class="ml-2 d-none d-lg-inline text-white small"><b>Login</b></span>
  </a>
  <!-- Uncomment this part if you want a dropdown menu for the profile options -->
  <!-- <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
    <a class="dropdown-item" href="../index.php">
      <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
      Profile
    </a>
    <a class="dropdown-item" href="../index.php">
      <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
      Settings
    </a>
    <a class="dropdown-item" href="../index.php">
      <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
      Activity Log
    </a>
    <div class="dropdown-divider"></div>
    <a class="dropdown-item" href="../index.php">
      <i class="fas fa-power-off fa-fw mr-2 text-danger"></i>
      Logout
    </a>
  </div> -->
</li>

          </ul>
        </nav>