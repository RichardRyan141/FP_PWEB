<?php
    session_start();
    include("config.php");

    if (!isset($_SESSION['login_user']) || $_SESSION['userType'] != 'Pemilik') {
        header("Location: index.php");
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
        <h1>Transaction Detail</h1>
        <table class="data-table">
            <?php
                $transactionId = $_GET['id'];
                $sql = "SELECT t.*, l.l_alamat, pe.pe_nama
                        FROM transaksi t
                        JOIN lokasi l ON l.l_id = t.Lokasi_l_id
                        JOIN pelanggan pe ON t.Pelanggan_pe_id = pe.pe_id
                        WHERE t.t_id = $transactionId";
                $result = mysqli_query($db, $sql);
                if ($result && mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);

                    echo "<table class='data-table'>";
                    echo "<tr><th>ID</th><td>" . $row['t_id'] . "</td></tr>";
                    echo "<tr><th>Time</th><td>" . $row['t_waktu'] . "</td></tr>";
                    echo "<tr><th>Address</th><td>" . $row['l_alamat'] . "</td></tr>";
                    echo "<tr><th>User</th><td>" . $row['pe_nama'] . "</td></tr>";
                    echo "<tr><th>Total Price</th><td>" . $row['t_hargaTotal'] . "</td></tr>";
                    echo "<tr><th>Notes</th><td>" . $row['t_keterangan'] ."</td></tr>";
                    echo "</table>";
                }
            ?>
        </table>

        <h1>Menu List</h1>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Menu Name</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total Price</th>
                </tr>
            </thead>
            <tbody id="data-table-body">
                <?php
                    $transactionId = $_GET['id'];
                    $sql = "SELECT mp.*, m.*
                            FROM transaksi t
                            JOIN menupesanan mp ON t.t_id = mp.Transaksi_t_id
                            JOIN menu m ON m.m_id = mp.Menu_m_id
                            WHERE t.t_id = $transactionId";
                    $result = mysqli_query($db, $sql);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . $row['m_nama'] . "</td>";
                        echo "<td>" . $row['mp_jumlahPesanan'] . "</td>";
                        echo "<td>" . $row['m_harga'] . "</td>";
                        echo "<td>" . $row['mp_jumlahPesanan']*$row['m_harga'] . "</td>";
                        echo "</tr>";
                    }
                ?>
        </table>
        <a href="report.php" class="button">Back to Report</a>
    </div>
</body>
</html>
