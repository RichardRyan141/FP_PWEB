<?php
    session_start();
    include("config.php");

    // Check if the user is logged in as Pemilik
    if (!isset($_SESSION['userType']) || $_SESSION['userType'] != "Pemilik") {
        header("location: index.php");
        exit();
    }

    $userType = isset($_SESSION['userType']) ? $_SESSION['userType'] : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Reservation</title>
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
        <h1>Create Stock</h1>
        <div class="form-container">
            <form method="" action="add_stok.php">
                <label for="name">Name: </label>
                <input type="text" name="name" id="name">
                
                <label for="quantity">Quantity: </label>
                <input type="number" name="quantity" id="quantity" min="0" value="0">
                
                <h3>Stock Usage</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Menu Name</th>
                            <th>Quantity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="menu_table">
                        <!-- Selected menus will be populated dynamically -->
                    </tbody>
                </table>
                
                <label for="menu">Menu:</label>
                <select name="menu" id="menu">
                    <?php
                        $menuQuery = "SELECT * FROM menu";
                        $menuResult = mysqli_query($db, $menuQuery);

                        // Display locations as options in the dropdown
                        while ($menu = mysqli_fetch_assoc($menuResult)) {
                            echo '<option value="' . $menu['m_id'] . '">' . $menu['m_nama'] . '</option>';
                        }
                        
                    ?>
                </select>
                
                <label for="quantityUsed">Quantity Used Per Portion:</label>
                <input type="number" name="quantityUsed" id="quantityUsed" min="1" value="1">
                
                <button type="button" onclick="addMenu()">Add Menu</button>
                <br>
                <input type="submit" name="confirm_stock" value="Confirm Stock" class="button">
            </form>
        </div>
    </div>
</body>
</html>
