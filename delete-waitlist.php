<?php
    // Include the necessary files and start the session
    session_start();
    include("config.php");

    // Check if user is logged in and has necessary privileges
    if (!isset($_SESSION['login_user']) || $_SESSION['userType'] == 'Pelanggan') {
        header("Location: login.php");
        exit();
    }

    if (isset($_GET['id'])) {
        $waitlistID = $_GET['id'];

        $deleteQuery = "DELETE FROM daftartunggu WHERE dt_id = '$waitlistID'";

        if (mysqli_query($db, $deleteQuery)) {
            header("Location: waitlist.php");
            exit();
        } else {
            $error = "Error deleting waitlist: " . mysqli_error($db);
        }
    } else {
        header("Location: waitlist.php");
        exit();
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Waitlist</title>
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
