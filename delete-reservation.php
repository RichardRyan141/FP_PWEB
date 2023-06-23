<?php
    // Include the necessary files and start the session
    session_start();
    include("config.php");

    $reservationId = $_GET['id'];

    // Check if the user is logged in as Pemilik
    if (!isset($_SESSION['userType']) || ($_SESSION['userType'] != "Pemilik" && $_SESSION['userType'] != "Pelanggan")) {
        header("location: index.php");
        exit();
    }

    $username = $_SESSION['login_user'];

    // Check if the user is logged in as Pemilik
    if ($_SESSION['userType'] == "Pelanggan") {
        $sql = "SELECT * FROM pelanggan WHERE pe_nama = '$username'";
        $result = mysqli_query($db, $sql);
        $dataCount = mysqli_num_rows($result);
        if ($dataCount == 0) {
            header("location: index.php");
            exit();
        }
    }

    $deleteQuery = "DELETE FROM menupesanan WHERE Transaksi_t_id = '$reservationId'";

    if (mysqli_query($db, $deleteQuery)) {
        //
    } else {
        $error = "Error deleting reservation menu: " . mysqli_error($db);
    }

    $deleteQuery = "DELETE FROM transaksi WHERE t_id = '$reservationId'";

    if (mysqli_query($db, $deleteQuery)) {
        header("Location: reservation-list.php");
        exit();
    } else {
        $error = "Error deleting reservation: " . mysqli_error($db);
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Reservation</title>
</head>
<body>
    <?php
        // Display error message if any
        if (isset($error)) {
            echo "<p>Error: $error</p>";
        }
    ?>
</body>
</html>
