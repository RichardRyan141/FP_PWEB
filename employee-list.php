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
            // Function to update the employee table based on selected filters
            function updateDataTable(location, position) {
                $.ajax({
                    url: "ajax-update-employee-table.php",
                    method: "GET",
                    data: { location: location, position: position },
                    success: function(data) {
                        $("#data-table-body").html(data);
                    }
                });
            }

            // Event listener for location filter
            $("#location").change(function() {
                var location = $(this).val();
                var position = $("#position").val();
                updateDataTable(location, position);
            });

            // Event listener for position filter
            $("#position").change(function() {
                var location = $("#location").val();
                var position = $(this).val();
                updateDataTable(location, position);
            });

            // Initially populate the employee table with all data
            updateDataTable("all", "all");

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
        <h2>Employee List</h2>

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

            <label for="position">Position:</label>
            <select id="position" name="position">
                <option value="all">All</option>
                <?php
                    // Fetch positions
                    $sql = "SELECT DISTINCT ha_namaLevel FROM hakakses";
                    $result = mysqli_query($db, $sql);

                    if ($result->num_rows > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<option value='" . $row['ha_namaLevel'] . "'>" . $row['ha_namaLevel'] . "</option>";
                        }
                    }
                ?>
            </select>
        </div>

        <br>

        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Location</th>
                    <?php if ($_SESSION['userType'] == "Pemilik") { ?>
                        <th>Actions</th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody id="data-table-body">
                <?php
                    $location = isset($_GET['location']) ? $_GET['location'] : 'all';
                    $position = isset($_GET['position']) ? $_GET['position'] : 'all';                

                    // Function to fetch and display employee data
                    function displayEmployeeData($db, $location, $position) {
                        // Construct the SQL query based on the selected filters
                        $sql = "SELECT k.k_id, k.k_nama, ha.ha_namaLevel, l.l_alamat 
                                FROM karyawan k
                                JOIN hakakses ha ON k.HakAkses_ha_id = ha.ha_id
                                JOIN lokasi l ON k.Lokasi_l_id = l.l_id";

                        if ($location !== 'all' && $position !== 'all') {
                            $sql .= " WHERE l.l_alamat = '$location' AND ha.ha_namaLevel = '$position'";
                        } elseif ($location !== 'all') {
                            $sql .= " WHERE l.l_alamat = '$location'";
                        } elseif ($position !== 'all') {
                            $sql .= " WHERE ha.ha_namaLevel = '$position'";
                        }

                        // Execute the query
                        $result = mysqli_query($db, $sql);

                        $counter = 1;
                        if ($result->num_rows > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . $counter . "</td>";
                                echo "<td>" . $row['k_nama'] . "</td>";
                                echo "<td>" . $row['ha_namaLevel'] . "</td>";
                                echo "<td>" . $row['l_alamat'] . "</td>";
                                if (($_SESSION['userType'] == "Pemilik") && ($row['ha_namaLevel'] !== "Pemilik")) {
                                    echo "<td>";
                                    echo "<a href='edit-employee.php?id=" . $row['k_id'] . "'>Edit</a> | ";
                                    echo "<a href='delete-employee.php?id=" . $row['k_id'] . "'>Delete</a>";
                                    echo "</td>";
                                }
                                
                                echo "</tr>";
                                $counter++;
                            }
                        } else {
                            echo "<tr><td colspan='5'>No employees found</td></tr>";
                        }
                    }

                    displayEmployeeData($db, $location, $position);
                ?>
            </tbody>
        </table>
        <a href="create-employee.php" class="button">Create New Employee</a>
    </div>

</body>
</html>
