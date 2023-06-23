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
        <h1>Monthly Report</h1>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Income</th>
                </tr>
            </thead>
            <tbody id="data-table-body">
                <?php
                    $firstTransactionQuery = "SELECT MIN(t_waktu) AS first_date FROM transaksi";
                    $firstTransactionResult = mysqli_query($db, $firstTransactionQuery);
                    $firstTransactionRow = mysqli_fetch_assoc($firstTransactionResult);
                    $firstDate = $firstTransactionRow['first_date'];
                    
                    $month = date('m', strtotime($firstDate));
                    $year = date('Y', strtotime($firstDate));

                    $currentMonth = date('m');
                    $currentYear = date('Y');

                    while ($year < $currentYear || ($year == $currentYear && $month <= $currentMonth)) {
                        $startDate = date('Y-m-01', strtotime("$year-$month-01"));
                        $endDate = date('Y-m-t', strtotime("$year-$month-01"));

                        $query = "SELECT *, SUM(t_hargaTotal) AS total_income
                                  FROM transaksi
                                  WHERE StatusTransaksi_st_id != 1 AND t_waktu >= '$startDate' AND t_waktu <= '$endDate' AND t_waktu <= NOW()";
                        $result = mysqli_query($db, $query);
                        $row = mysqli_fetch_assoc($result);
                        
                        $dateTime = DateTime::createFromFormat('!m', $month);
                        $monthName = $dateTime->format('F');
                        
                        echo "<tr>";
                        echo "<td>" . $monthName . " " . $year . "</td>";
                        echo "<td>" . $row['total_income'] . "</td>";
                        echo "</tr>";

                        $month++;
                        if ($month > 12) {
                            $month = 1;
                            $year++;
                        }
                    
                    }
                ?>
            </tbody>
        </table>
        <a href="report.php" class="button">Back to Report</a>
    </div>
</body>
</html>
