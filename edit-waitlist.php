<?php
    session_start();
    include("config.php");

    // Check if the user is logged in and has the necessary permissions
    if (!isset($_SESSION['login_user']) || $_SESSION['userType'] == "Pelanggan") {
        header("location: index.php");
        exit();
    }

    $userType = isset($_SESSION['userType']) ? $_SESSION['userType'] : '';

    if ((!isset($_GET['id']))) {
        header("Location: waitlist.php");
    }

    // Get the employee ID from the URL parameter
    $waitlistID = $_GET['id'];

    // Fetch the employee data from the database
    $sql = "SELECT * FROM daftartunggu WHERE dt_id = '$waitlistID'";
    $result = mysqli_query($db, $sql);
    $waitlistData = mysqli_fetch_assoc($result);
    $waitlistAddress = $waitlistData['Lokasi_l_id'];

    $locationQuery = "SELECT * FROM lokasi WHERE l_id = '$waitlistAddress'";
    $locationResult = mysqli_query($db, $locationQuery);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Retrieve form data
        $waitlistID = $_POST['id'];
        $dt_jumlahOrang = $_POST['dt_jumlahOrang'];

        $updateQuery = "UPDATE daftartunggu SET dt_jumlahOrang = '$dt_jumlahOrang' WHERE dt_id = '$waitlistID'";
        
        if (mysqli_query($db, $updateQuery)) {
            // Redirect to table list page after successful update
            header("location: waitlist.php");
            exit();
        } else {
            // Handle database update error
            $errorMessage = "Error: " . mysqli_error($db);
        }
    }
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

    <div class="collapsed-menu-bar"></div>
    
    <div class="form-container">
        <h2>Edit Waitlist</h2>
        <form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
            <input type="hidden" name="id" value="<?php echo $waitlistID; ?>">
            
            <label for="dt_namaPelanggan">Customer Name:</label>
            <input type="text" id="dt_namaPelanggan" name="dt_namaPelanggan" value="<?php echo $waitlistData['dt_namaPelanggan']; ?>" readonly>

            <label for="dt_jumlahOrang">Number of People:</label>
            <input type="number" id="dt_jumlahOrang" name="dt_jumlahOrang" value="<?php echo $waitlistData['dt_jumlahOrang']; ?>">

            <label for="location">Location:</label>
            <select id="location" name="location">
                <?php
                    while ($row = mysqli_fetch_assoc($locationResult)) {
                        $selected = ($row['l_id'] == $waitlistData['Lokasi_l_id']) ? "selected" : "";
                        echo "<option value='" . $row['l_id'] . "' " . $selected . ">" . $row['l_alamat'] . "</option>";
                    }
                ?>
            </select>

            
            <input type="submit" value="Update Waitlist" class="button">
        </form>
    </div>
</body>
</html>
