<?php
    session_start();
    include("config.php");

    // Check if the user is logged in and has the necessary permissions
    if (!isset($_SESSION['login_user']) || $_SESSION['userType'] !== "Pemilik") {
        header("location: employee-list.php");
        exit();
    }

    if ((!isset($_GET['id']))) {
        header("Location: employee-list.php");
    }

    $userType = isset($_SESSION['userType']) ? $_SESSION['userType'] : '';
    // Get the employee ID from the URL parameter
    $employeeID = $_GET['id'];

    // Fetch the employee data from the database
    $sql = "SELECT * FROM karyawan WHERE k_id = '$employeeID'";
    $result = mysqli_query($db, $sql);
    $employeeData = mysqli_fetch_assoc($result);

    // Fetch the positions (except Pemilik) and locations for the dropdown lists
    $positionQuery = "SELECT ha_id, ha_namaLevel FROM hakakses WHERE ha_namaLevel != 'Pemilik'";
    $positionResult = mysqli_query($db, $positionQuery);

    $locationQuery = "SELECT l_id, l_alamat FROM lokasi";
    $locationResult = mysqli_query($db, $locationQuery);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Restaurant Website</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $(".menu-toggle").click(function() {
                $(".menu-bar").toggleClass("collapsed");
                $(".collapsed-menu-bar").toggleClass("collapsed");
                $(".menu-bar ul li").toggleClass("collapsed");
            });
        });
    </script>
</head>
<body>
    <div class="top-bar">
        <?php
            // Check if the user is logged in
            $loggedIn = isset($_SESSION['login_user']);
            
            if ($loggedIn) {
                $username = $_SESSION['login_user'];
                if ($userType != "Pelanggan") {
                    echo "<a href='notification.php'><img src='https://i.ibb.co/YRSpMcL/notif-removebg-preview.png' alt='Notification Icon'></a>";
                }
                echo "Hi, $username | <a href='logout.php'>Logout</a>";
            } else {
                echo "<a href='login.php'>Login</a> | <a href='create-user.php'>Create Account</a>";
            }
        ?>
    </div>

    <div class="menu-bar">
        <div class="menu-toggle">
            <div class="bar"></div>
            <div class="bar middle"></div>
            <div class="bar"></div>
        </div>
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="menu-list.php">Menu List</a></li>
            <li><a href="promo-list.php">Promo</a></li>
            <?php
                if ($loggedIn) {
                    // Show reservations menu for logged in users
                    echo "<li>Reservations";
                    echo "<ul>";
                    echo "<li><a href='reservation-list.php'>Reservation List</a></li>";
                    
                    // Show "Make a reservation" menu for Pelanggan users
                    if ($_SESSION['userType'] == "Pelanggan") {
                        echo "<li><a href='create-reservation.php'>Make a Reservation</a></li>";
                    }
                    
                    echo "</ul>";
                    echo "</li>";

                    if ($_SESSION['userType'] != "Pelanggan") {
                        echo "<li><a href='waitlist.php'>Wait List</a></li>";
                    }

                    // Show "Report" and "Employee List" menu for "Pemilik" user
                    if ($_SESSION['userType'] == "Pemilik") {
                        echo "<li><a href='report.php'>Report</a></li>";
                        echo "<li><a href='employee-list.php'>Employee List</a></li>";
                        echo "<li><a href='member-list.php'>Member List</a></li>";
                    }

                    if ($_SESSION['userType'] == "Pemilik" || $_SESSION['userType'] == "Direktur" || $_SESSION['userType'] == "Manager") {
                        echo "<li><a href='stok.php'>Stok</a></li>";
                    }
                }
                
                echo "<li><a href='review-list.php'>Reviews</a></li>";
            ?>
            <li><a href="meja-list.php">Status Meja</a></li>
        </ul>
    </div>

    <div class="form-container">
        <h2>Edit Employee</h2>
        <form action="update-employee-data.php" method="POST">
            <input type="hidden" name="employeeID" value="<?php echo $employeeID; ?>">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo $employeeData['k_nama']; ?>" readonly>
            
            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" value="<?php echo $employeeData['k_noTelepon']; ?>" readonly>
            
            <label for="address">Address:</label>
            <input type="text" id="address" name="address" value="<?php echo $employeeData['k_alamat']; ?>" readonly>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo $employeeData['k_email']; ?>" readonly>
            
            <label for="position">Position:</label>
            <select id="position" name="position">
                <?php
                    while ($row = mysqli_fetch_assoc($positionResult)) {
                        $selected = ($row['ha_id'] == $employeeData['HakAkses_ha_id']) ? "selected" : "";
                        echo "<option value='" . $row['ha_id'] . "' " . $selected . ">" . $row['ha_namaLevel'] . "</option>";
                    }
                ?>
            </select>
            
            <label for="location">Location:</label>
            <select id="location" name="location">
                <?php
                    while ($row = mysqli_fetch_assoc($locationResult)) {
                        $selected = ($row['l_id'] == $employeeData['Lokasi_l_id']) ? "selected" : "";
                        echo "<option value='" . $row['l_id'] . "' " . $selected . ">" . $row['l_alamat'] . "</option>";
                    }
                ?>
            </select>
            
            <input type="submit" value="Update Employee" class="button">
        </form>
    </div>
</body>
</html>
