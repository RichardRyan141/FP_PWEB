<?php
    // Include the necessary files and start the session
    session_start();
    include("config.php");

    // Check if the user is logged in as Pemilik
    if (!isset($_SESSION['login_user']) || $_SESSION['userType'] != "Pemilik") {
        header("location: index.php");
        exit();
    }
    
    $userType = isset($_SESSION['userType']) ? $_SESSION['userType'] : '';
    $memberId = $_GET['id'];

    // Retrieve the email template from the database
    $sql = "SELECT * FROM pelanggan WHERE pe_id = '$memberId'";
    $result = mysqli_query($db, $sql);
    $row = mysqli_fetch_assoc($result);
    $templateEmail = $row['pe_templateEmail'];

    // Update the email template
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $templateEmail = $_POST['templateEmail'];
        $sql = "UPDATE pelanggan SET pe_templateEmail = '$templateEmail' WHERE pe_id = '$memberId'";
        mysqli_query($db, $sql);
        header("location: member-list.php");
        exit();
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Change Email Template</title>
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
    
    <div class="content">
        <h1>Change Email Template</h1>
        <div class="form-group">
            <form method="POST">
                    <label class="label" for="name">Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo $row['pe_nama']; ?>" readonly class="text-box">
                    <label class="label" for="templateEmail">Email Template:</label>
                    <textarea id="templateEmail" name="templateEmail" rows="8" class="text-box"><?php echo $templateEmail; ?></textarea>
                <input type="submit" value="Save Template">
            </form>
        </div>
    </div>

</body>
</html>
