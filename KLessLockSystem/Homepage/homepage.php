<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <link href="img/logo/qc.png" rel="icon">
    <title>Dashboard</title>

    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="css/ruang-admin.min.css" rel="stylesheet">
    <style>
    html,
    body {
        margin: 0;
        padding: 0;
        height: 100%;
        width: 100%;
        background: url('img/logo/background.jpg') no-repeat center center fixed;
        background-size: cover;
        overflow: hidden;
        color: white;
    }

    .centered-heading {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh; /* Keeps the centering */
        text-align: center;
    }

    .logo-and-title {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
        min-height: 65vh; /* This is the key change */
    }

    .logo {
        max-height: 300px; /* Adjust as needed */
        height: auto;
    }

    h1 {
        color: #f8f9fc;
        text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7);
        font-size: 2.5rem;
        margin: 0;
    }
</style>
</head>

<body>
    <?php include "Includes/topbar.php"; ?>
    <div class="centered-heading">
        <div class="logo-and-title"> <img src="img/logo/kl.png" alt="KLessLock Logo" class="logo">
            <h1>Welcome to KLessLock System</h1>
        </div>
    </div>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/ruang-admin.min.js"></script>
    <script src="../vendor/chart.js/Chart.min.js"></script>
</body>

</html>