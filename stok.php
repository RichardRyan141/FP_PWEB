<?php
    session_start();
    include("config.php");

    if (!isset($_SESSION['login_user']) || $_SESSION['userType'] != "Pemilik") {
        header("location: index.php");
        exit();
    }

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
    <h2>Stock List</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Name</th>
                    <th>Amount</th>
                    <th>Average Daily Usage</th>
                    <th>Time Remaining until Critical</th>
                    <?php
                        if (($userType == "Pemilik") || ($userType == "Pemilik")) {
                            echo "<th>Action</th>";
                        }
                    ?>
                </tr>
            </thead>
            <tbody id="data-table-body">
                <?php
                    $firstTransactionQuery = "SELECT MIN(t_waktu) AS first_transaction FROM transaksi";
                    $firstTransactionResult = mysqli_query($db, $firstTransactionQuery);
                    $firstTransactionData = mysqli_fetch_assoc($firstTransactionResult);
                    $startDate = $firstTransactionData['first_transaction'];
                    $endDate = date("Y-m-d"); // Current date
                    $totalDays = (strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24); // Calculate the total number of days

                    // Query to calculate the total stock usage
                    $sql = "SELECT s.s_id, s.s_nama, s.s_jumlahStok, 
                            SUM(mp.mp_jumlahPesanan * dps.dps_jumlahStokPerPorsi) AS total_usage
                            FROM stok s
                            JOIN detailpenggunaanstok dps ON s.s_id = dps.Stok_s_id
                            JOIN menupesanan mp ON mp.mp_id = dps.Menu_m_id
                            GROUP BY s.s_id, s.s_nama, s.s_jumlahStok";

                    $result = mysqli_query($db, $sql);
                    $counter = 1;
                    if ($result->num_rows > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $counter . "</td>";
                            echo "<td>" . $row['s_nama'] . "</td>";
                            echo "<td>" . $row['s_jumlahStok'] . "</td>";

                            $averageUsage = $row['total_usage'] / $totalDays;
                            $remainingTime = $row['s_jumlahStok'] / $averageUsage - 3;

                            echo "<td>" . $averageUsage . "</td>";
                            echo "<td>" . $remainingTime . "</td>";

                            echo "<td>";
                            if (($userType == "Pemilik") || ($userType == "Pemilik")) {
                                echo "<a href='edit-stok.php?id=" . $row['s_id'] . "'>Edit</a>";
                            }
                            echo "</td>";
                            $counter++;
                        }
                    } else {
                        echo "<tr><td colspan='6'>No stock found</td></tr>";
                    }
                ?>
            </tbody>
        </table>
        <a href="create-stok.php" class="button">Create Stock</a>
    </div>
</body>
</html>
