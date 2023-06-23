<?php
    session_start();
    include("config.php");

    $userType = isset($_SESSION['userType']) ? $_SESSION['userType'] : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Restaurant Website</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Menu toggle functionality
            $(".menu-toggle").click(function() {
                $(".menu-bar").toggleClass("collapsed");
                $(".collapsed-menu-bar").toggleClass("collapsed");
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
        <h1>Promo List</h1>
        <h3>Active Promo</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Detail</th>
                    <th>Discount (%)</th>
                    <th>Max Discount</th>
                    <th>Expired</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody id="data-table-body">
                <?php
                    $currentDate = date('Y-m-d');
                    $sql = "SELECT * FROM promosi WHERE pr_expired >= '$currentDate' AND pr_persenDiskon != 0 ORDER BY pr_expired ASC";
                    $result = mysqli_query($db, $sql);
                    $counter = 1;
                    if ($result->num_rows > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $counter . "</td>";
                            echo "<td>" . $row['pr_detail'] . "</td>";
                            echo "<td>" . $row['pr_persenDiskon'] . "</td>";
                            echo "<td>" . $row['pr_maxDiskon'] . "</td>";
                            echo "<td>" . date('d-m-Y', strtotime($row['pr_expired'])) . "</td>";
                            echo "<td>" . $row['pr_keterangan'] . "</td>";
                            echo "</tr>";
                            $counter++;
                        }
                    } else {
                        echo "<tr><td colspan='6'>No promotion found</td></tr>";
                    }
                ?>
            </tbody>
        </table>

        <h3>Expired Promo</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Detail</th>
                    <th>Discount (%)</th>
                    <th>Max Discount</th>
                    <th>Expired</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody id="data-table-body">
                <?php
                    $currentDate = date('Y-m-d');
                    $sql = "SELECT * FROM promosi WHERE pr_expired < '$currentDate' AND pr_persenDiskon != 0 ORDER BY pr_expired ASC";
                    $result = mysqli_query($db, $sql);
                    $counter = 1;
                    if ($result->num_rows > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $counter . "</td>";
                            echo "<td>" . $row['pr_detail'] . "</td>";
                            echo "<td>" . $row['pr_persenDiskon'] . "</td>";
                            echo "<td>" . $row['pr_maxDiskon'] . "</td>";
                            echo "<td>" . date('d-m-Y', strtotime($row['pr_expired'])) . "</td>";
                            echo "<td>" . $row['pr_keterangan'] . "</td>";
                            echo "</tr>";
                            $counter++;
                        }
                    } else {
                        echo "<tr><td colspan='6'>No promotion found</td></tr>";
                    }
                ?>
            </tbody>
        </table>
        <?php
        if ($userType == "Pemilik") {
            echo "<a href='create-promo.php' class='button'>Create New Promo</a>";
        }
        ?>
    </div>
</body>
</html>
