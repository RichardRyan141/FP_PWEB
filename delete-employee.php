<?php
    // Include the necessary files and start the session
    session_start();
    include("config.php");

    // Check if user is logged in and has necessary privileges
    if (!isset($_SESSION['login_user']) || $_SESSION['userType'] != 'Pemilik') {
        header("Location: index.php");
        exit();
    }

    // Process the deletion if the employee ID is provided
    if (isset($_GET['id'])) {
        $employeeId = $_GET['id'];

        // Delete the employee from the database
        $deleteQuery = "DELETE FROM karyawan WHERE k_id = '$employeeId'";

        if (mysqli_query($db, $deleteQuery)) {
            // Employee deleted successfully, redirect to employee list page
            header("Location: employee-list.php");
            exit();
        } else {
            $error = "Error deleting employee: " . mysqli_error($db);
        }
    } else {
        header("Location: employee-list.php");
        exit();
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Employee</title>
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
