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
        $menuId = $_GET['id'];

        $query = "SELECT * FROM menu WHERE m_id = '$menuId'";
        $result = mysqli_query($db, $query);
        $row = mysqli_fetch_assoc($result);
        $oldPicture = $row['m_foto'];
        if (!empty($oldPicture)) {
            unlink($oldPicture); // Delete the old photo
        }

        $deleteQuery = "DELETE FROM menu WHERE m_id = '$menuId'";

        if (mysqli_query($db, $deleteQuery)) {
            header("Location: menu-list.php");
            exit();
        } else {
            $error = "Error deleting menu: " . mysqli_error($db);
        }
    } else {
        header("Location: menu-list.php");
        exit();
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Menu</title>
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
