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
            function updateDataTable(location) {
                $.ajax({
                    url: "ajax-update-review-list.php",
                    method: "GET",
                    data: { location: location},
                    success: function(data) {
                        $("#review").html(data);
                    }
                });
            }

            // Event listener for location filter
            $("#location").change(function() {
                var location = $(this).val();
                updateDataTable(location);
            });

            // Initially populate the employee table with all data
            updateDataTable("all");

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
        <h2>Review List</h2>
        <div>
            <label for="location">Location:</label>
            <select id="location" name="location">
                <option value="all">All</option>
                <?php
                    // Fetch locations
                    $sql = "SELECT DISTINCT l_alamat FROM lokasi";
                    $result = mysqli_query($db, $sql);

                    if ($result->num_rows > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<option value='" . $row['l_alamat'] . "'>" . $row['l_alamat'] . "</option>";
                        }
                    }
                ?>
            </select>
        </div>

        <br>

        <div id="review">
            <?php
                $location = isset($_GET['location']) ? $_GET['location'] : 'all';

                function displayReviewData($db, $location) {
                    $sql = "SELECT rv.rv_detail, l.l_alamat 
                            FROM review rv
                            JOIN transaksi t ON t.t_id = rv.Transaksi_t_id
                            JOIN lokasi l ON l.l_id = t.Lokasi_l_id"; 
                    
                    if ($location !== 'all') {
                        $sql .= " WHERE l.l_alamat = '$location'";
                    }

                    $result = mysqli_query($db, $sql);

                    if ($result->num_rows > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<div class='review'>";
                            echo "<p>Location: {$row['l_alamat']}</p>";
                            echo "<p>Review : {$row['rv_detail']}</p>";
                            echo "</div>";
                        }
                    }
                    else {
                        echo "<p>No reviews available </p>";
                    }
                }

                displayReviewData($db, $location);
            ?>
        </div>
    </div>
</body>
</html>
