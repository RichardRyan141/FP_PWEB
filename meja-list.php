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
                    url: "ajax-update-seat-table.php",
                    method: "GET",
                    data: { location: location},
                    success: function(data) {
                        $("#data-table-body").html(data);
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
        <h2>Table List</h2>

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

        <table class="data-table">
            <thead>
                <tr>
                    <th>Table No</th>
                    <th>Status</th>
                    <th>Location</th>
                    <?php 
                        if (($userType !== "") && ($userType !== "Pelanggan")) {
                            echo "<th>Actions</th>";
                        } 
                    ?>
                </tr>
            </thead>
            <tbody id="data-table-body">
                <?php
                    $location = isset($_GET['location']) ? $_GET['location'] : 'all';

                    // Function to fetch and display employee data
                    function displayTableData($db, $location) {
                        // Construct the SQL query based on the selected filters
                        $sql = "SELECT dm.dm_id, dm.dm_noMeja, sm_statusMeja, l_alamat
                                FROM daftarmeja dm
                                JOIN statusmeja sm ON dm.StatusMeja_sm_id = sm.sm_id
                                JOIN lokasi l ON dm.Lokasi_l_id = l.l_id";

                        if ($location !== 'all') {
                            $sql .= " WHERE l.l_alamat = '$location'";
                        }

                        // Execute the query
                        $result = mysqli_query($db, $sql);

                        $userType = isset($_SESSION['userType']) ? $_SESSION['userType'] : '';

                        if ($result->num_rows > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . $row['dm_noMeja'] . "</td>";
                                echo "<td>" . $row['sm_statusMeja'] . "</td>";
                                echo "<td>" . $row['l_alamat'] . "</td>";
                                if ($userType == "Pemilik" || $userType == "Direktur" || $userType == "Manager" || $userType == "Pegawai") {
                                    echo "<td>";
                                    echo "<a href='edit-table-status.php?id=" . $row['dm_id'] . "'>Edit</a>";
                                    if ($userType == "Pemilik")
                                    {
                                        echo " | <a href='delete-table.php?id=" . $row['dm_id'] . "'>Delete</a>";
                                    }
                                    echo "</td>";
                                }
                                
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No table found</td></tr>";
                        }
                    }

                    displayTableData($db, $location);
                ?>
            </tbody>
        </table>
        <?php
            if ($userType == "Pemilik" || $userType == "Direktur" || $userType == "Manager" || $userType == "Pegawai") {
                echo "<a href='create-table.php' class='button'>Create New Table</a>";
            }
        ?>
    </div>
</body>
</html>
