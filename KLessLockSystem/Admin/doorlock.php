
<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';



?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <!-- <link href="img/logo/attnlg.jpg" rel="icon"> -->
<?php include 'includes/title.php';?>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">



   <script>
    function classArmDropdown(str) {
    if (str == "") {
        document.getElementById("txtHint").innerHTML = "";
        return;
    } else { 
        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("txtHint").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET","ajaxClassArms2.php?cid="+str,true);
        xmlhttp.send();
    }
}
</script>
<style>
    .main-container {
        display: flex;
        flex-wrap: wrap;
        box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.05);
        padding: 10px;
        margin-top: 10px;
        border-radius: 4px;
        width: 100%;
        max-width: 1200px;
        border: 1px solid gray;
        background-color: #f5f5f5;
    }

    .control-panel {
        flex: 1 1 100%;
        padding: 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-bottom: 1px solid gray;
    }

    .control-panel img {
        width: 100px;
        height: 100px;
        margin-bottom: 10px;
    }

    .info-panel {
        flex: 1 1 100%;
        padding: 10px;
    }

    .status-box,
    .alert-box {
        background-color: #f0f0f0;
        margin-bottom: 10px;
        padding: 10px;
        border-radius: 3px;
        font-size: 0.75rem; /* Smaller font size */
    }

    .status-box h3,
    .alert-box h3 {
        margin-top: 0;
        font-size: 0.875rem; /* Smaller heading font size */
    }

    .button-scan {
        background-color: green;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 3px;
        font-size: 0.75rem; /* Smaller button font */
        cursor: pointer;
    }

    .button-scan:hover {
        background-color: darkgreen;
    }

    @media (min-width: 768px) {
        .main-container {
            flex-wrap: nowrap;
        }

        .control-panel {
            flex: 1 1 50%;
            border-bottom: none;
            border-right: 1px solid gray;
        }

        .info-panel {
            flex: 1 1 50%;
        }
    }
</style>



</head>

<body id="page-top">
  <div id="wrapper">
    <!-- Sidebar -->
      <?php include "Includes/sidebar.php";?>
    <!-- Sidebar -->
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <!-- TopBar -->
       <?php include "Includes/topbar.php";?>
        <!-- Topbar -->

        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">
      
</div>
<div class="container-fluid" id="container-wrapper">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Door Lock Setting</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="./">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">DoorLock Settings</li>
        </ol>
    </div>

    <div class="row">
        <!-- Borrowing Form -->
      <div class="col-lg-12">
    <div class="card mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Add iPads</h6>
        </div>
        <div class="card-body">
            <form action="borrow_ipad.php" method="post">
                <div class="form-row align-items-end">
                   
                  
                <div class="container">
          <div class="main-container">
            <div class="control-panel">
              <h2>Control Panel</h2>
              <img src="img/scan.png" alt="Fingerprint Icon">


              <p>Place your Finger on the scanner</p>
              <button class="button-scan" onclick="startScan()">Start Scan</button>
            </div>
            <div class="info-panel">
              <div class="status-box">
                <h3>Status</h3>
                <p><strong>Door Lock:</strong> Lock</p>
                <p><strong>Last Door Entry:</strong> Louie Aguilar</p>
                <p><strong>Battery Status:</strong> 90%</p>
                <p><strong>Last Unlock:</strong> May 9, 2024</p>
              </div>
              <div class="alert-box">
                <h3>Alert and Notification</h3>
                <div id="alerts">
                  <p>We notice a new Request login from unauthorized user.</p>
                  <p><small>May 10, 2024 at 11:30 am</small></p>
                  <p>We notice a new Request login from unauthorized user.</p>
                  <p><small>May 10, 2024 at 10:03 am</small></p>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
                </div>
            </form>
        </div>
    </div>
</div>


    

     

              

          <!-- Documentation Link -->
          <!-- <div class="row">
            <div class="col-lg-12 text-center">
              <p>For more documentations you can visit<a href="https://getbootstrap.com/docs/4.3/components/forms/"
                  target="_blank">
                  bootstrap forms documentations.</a> and <a
                  href="https://getbootstrap.com/docs/4.3/components/input-group/" target="_blank">bootstrap input
                  groups documentations</a></p>
            </div>
          </div> -->

        </div>
        <!---Container Fluid-->
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