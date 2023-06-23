<?php
    session_start();
    include("config.php");

    if (!isset($_SESSION['login_user']) || $_SESSION['userType'] !== "Pemilik") {
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
        <h2>Transaction List</h2>
        <a href='monthly-report.php' class="button">Go to Monthly Report</a>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Time</th>
                    <th>Address</th>
                    <th>User</th>
                    <th>Total Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="data-table-body">
                <?php
                    $sql = "SELECT t.*
                            FROM transaksi t
                            WHERE StatusTransaksi_st_id != 1 AND t_waktu < NOW()
                            ORDER BY t_waktu";
                    $result = mysqli_query($db, $sql);
                    $counter = 1;
                    if ($result->num_rows > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $counter . "</td>";
                            echo "<td>" . $row['t_waktu'] . "</td>";

                            $locID = $row['Lokasi_l_id'];
                            $locationQuery = "SELECT l_alamat
                                              FROM lokasi
                                              WHERE l_id = $locID";
                            $locationResult = mysqli_query($db, $locationQuery);
                            $locationRow = mysqli_fetch_assoc($locationResult);

                            echo "<td>" . $locationRow['l_alamat'] . "</td>";

                            $userID = $row['Pelanggan_pe_id'];
                            $userQuery = "SELECT pe_nama
                                          FROM pelanggan
                                          WHERE pe_id = $userID";
                            $userResult = mysqli_query($db, $userQuery);
                            $userRow = mysqli_fetch_assoc($userResult);

                            echo "<td>" . $userRow['pe_nama'] . "</td>";
                            echo "<td>" . $row['t_hargaTotal'] . "</td>";
                            echo "<td><a href='detail-transaction.php?id=" . $row['t_id'] . "'>Details</a></td>";
                            echo "</tr>"; 
                            $counter++;
                        }
                    } else {
                        echo "<tr><td colspan='6'>No Reservation found</td></tr>";
                    }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
