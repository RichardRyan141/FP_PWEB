<?php
    // Include the necessary files and start the session
    session_start();
    include("config.php");

    // Check if user is logged in and has necessary privileges
    if (!isset($_SESSION['login_user']) || $_SESSION['userType'] != 'Pemilik') {
        header("Location: index.php");
        exit();
    }

    if (isset($_GET['id'])) {
        $memberId = $_GET['id'];

        $deleteQuery = "DELETE FROM pelanggan WHERE pe_id = '$memberId'";

        if (mysqli_query($db, $deleteQuery)) {
            header("Location: member-list.php");
            exit();
        } else {
            $error = "Error deleting member: " . mysqli_error($db);
        }
    } else {
        header("Location: member-list.php");
        exit();
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Member</title>
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
