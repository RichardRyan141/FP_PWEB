<?php
    // Include the necessary files and start the session
    session_start();
    include("config.php");

    // Check if the user is logged in as Pemilik
    if (!isset($_SESSION['login_user']) || $_SESSION['userType'] != "Pemilik") {
        header("location: index.php");
        exit();
    }

    $userType = isset($_SESSION['userType']) ? $_SESSION['userType'] : '';

    // Define variables and set them empty initially
    $k_nama = $k_alamat = $k_email = $k_password = $k_noTelepon = $ha_id = $l_id = "";
    $error = "";

    // Process the form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate and sanitize the input data
        $k_nama = validateInput($_POST["k_nama"]);
        $k_alamat = validateInput($_POST["k_alamat"]);
        $k_email = validateInput($_POST["k_email"]);
        $k_password = validateInput($_POST["k_password"]);
        $k_noTelepon = validateInput($_POST["k_noTelepon"]);
        $ha_id = validateInput($_POST["HakAkses_ha_id"]);
        $l_id = validateInput($_POST["Lokasi_l_id"]);

        // Insert the employee data into the database
        $insertQuery = "INSERT INTO karyawan (k_nama, k_alamat, k_email, k_password, k_noTelepon, HakAkses_ha_id, Lokasi_l_id)
                        VALUES ('$k_nama', '$k_alamat', '$k_email', '$k_password', '$k_noTelepon', '$ha_id', '$l_id')";

        if (mysqli_query($db, $insertQuery)) {
            // Employee created successfully, redirect to employee list page
            header("Location: employee-list.php");
            exit();
        } else {
            $error = "Error creating employee: " . mysqli_error($db);
        }
    }

    // Function to validate and sanitize input data
    function validateInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Employee</title>
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
        <h1>Create Employee</h1>
        <div class="form-container">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div>
                    <label for="k_nama">Employee Name *:</label>
                    <input type="text" id="k_nama" name="k_nama" required>
                </div>

                <div>
                    <label for="k_alamat">Employee Address:</label>
                    <input type="text" id="k_alamat" name="k_alamat">
                </div>

                <div>
                    <label for="k_email">Employee Email *:</label>
                    <input type="email" id="k_email" name="k_email" required>
                </div>

                <div>
                    <label for="k_password">Employee Password *:</label>
                    <input type="password" id="k_password" name="k_password" required>
                </div>

                <div>
                    <label for="k_noTelepon">Phone Number *:</label>
                    <input type="text" id="k_noTelepon" name="k_noTelepon" required>
                </div>

                <div>
                    <label for="HakAkses_ha_id">Access Level *:</label>
                    <select id="HakAkses_ha_id" name="HakAkses_ha_id" required>
                        <?php
                            // Retrieve access levels from the database
                            $accessLevelsQuery = "SELECT * FROM hakakses";
                            $accessLevelsResult = mysqli_query($db, $accessLevelsQuery);

                            // Display access levels as options in the dropdown
                            while ($accessLevel = mysqli_fetch_assoc($accessLevelsResult)) {
                                echo "<option value='" . $accessLevel['ha_id'] . "'>" . $accessLevel['ha_namaLevel'] . "</option>";
                            }
                        ?>
                    </select>
                </div>

                <div>
                    <label for="Lokasi_l_id">Location *:</label>
                    <select id="Lokasi_l_id" name="Lokasi_l_id" required>
                        <?php
                            // Retrieve locations from the database
                            $locationsQuery = "SELECT * FROM lokasi";
                            $locationsResult = mysqli_query($db, $locationsQuery);

                            // Display locations as options in the dropdown
                            while ($location = mysqli_fetch_assoc($locationsResult)) {
                                echo "<option value='" . $location['l_id'] . "'>" . $location['l_alamat'] . "</option>";
                            }
                        ?>
                    </select>
                </div>

                <div>
                    <input type="submit" value="Create Employee" class="button">
                </div>
            </form>
        </div>

        <?php
            // Display error message if any
            if ($error) {
                echo "<p>Error: $error</p>";
            }
        ?>

    </div>
</body>
</html>
