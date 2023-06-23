<?php
session_start();
include("config.php");

// Check if the user is logged in and has the necessary permissions
if (!isset($_SESSION['login_user']) || $_SESSION['userType'] != "Pemilik") {
    header("location: employee-list.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the employee ID from the form
    $employeeID = $_POST['employeeID'];

    // Retrieve the updated employee details from the form
    $positionID = $_POST['position'];
    $locationID = $_POST['location'];

    // Update the employee record in the database
    $updateQuery = "UPDATE karyawan SET HakAkses_ha_id = '$positionID', Lokasi_l_id = '$locationID' WHERE k_id = '$employeeID'";
    mysqli_query($db, $updateQuery);

    // Redirect to the employee list page after successful update
    header("location: employee-list.php");
    exit();
}
?>
