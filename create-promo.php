<?php
    session_start();
    include("config.php");
    
    // Redirect to login page if user is not logged in or not of type "Pemilik"
    if (!isset($_SESSION['login_user']) || $_SESSION['userType'] !== "Pemilik") {
        header("location: promo-list.php");
        exit();
    }

    $userType = isset($_SESSION['userType']) ? $_SESSION['userType'] : '';

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Retrieve form data
        $promoName = $_POST["promo_name"];
        $promoPercentage = $_POST["promo_percentage"];
        $promoMaxDiscount = $_POST["promo_max_discount"];
        $promoExpiration = $_POST["promo_expiration"];

        // Insert promo into database
        $sql = "INSERT INTO promosi (pr_detail, pr_persenDiskon, pr_maxDiskon, pr_expired) 
                VALUES ('$promoName', '$promoPercentage', '$promoMaxDiscount', '$promoExpiration')";
        
        if (mysqli_query($db, $sql)) {
            // Redirect to promo list page after successful insertion
            header("location: promo-list.php");
            exit();
        } else {
            // Handle database insertion error
            $errorMessage = "Error: " . mysqli_error($db);
        }
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Promo</title>
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
        <h2>Create Promo</h2>
        <?php
            // Display error message, if any
            if (isset($errorMessage)) {
                echo "<p>$errorMessage</p>";
            }
        ?>
        <div class="form-container">
            <form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
                <label for="promo_name">Promo Name:</label>
                <input type="text" id="promo_name" name="promo_name" required>

                <label for="promo_percentage">Percentage Discount:</label>
                <input type="number" id="promo_percentage" name="promo_percentage" min="0" max="100" required>

                <label for="promo_max_discount">Maximum Discount:</label>
                <input type="number" id="promo_max_discount" name="promo_max_discount" min="0" required>

                <label for="promo_expiration">Expiration Date:</label>
                <input type="date" id="promo_expiration" name="promo_expiration" required>

                <button type="submit">Create Promo</button>
            </form>
        </div>
    </div>
</body>
</html>
