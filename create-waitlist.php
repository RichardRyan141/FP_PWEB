<?php
    // Include the necessary files and start the session
    session_start();
    include("config.php");

    if (!isset($_SESSION['userType']) || $_SESSION['userType'] == "Pelanggan") {
        header("location: index.php");
        exit();
    }

    $userType = isset($_SESSION['userType']) ? $_SESSION['userType'] : '';

    // Define variables and set them empty initially
    $dt_namaPelanggan = $dt_jumlahOrang = $l_id = "";
    $error = "";

    $username = $_SESSION['login_user'];

    $sql = "SELECT k_id
            FROM karyawan
            WHERE k_nama = '$username'";
    $result = mysqli_query($db, $sql);
    $employeeData = mysqli_fetch_assoc($result);
    $employeeID = $employeeData['k_id'];

    // Process the form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate and sanitize the input data
        $dt_namaPelanggan = validateInput($_POST["dt_namaPelanggan"]);
        $dt_jumlahOrang = validateInput($_POST["dt_jumlahOrang"]);
        $l_id = validateInput($_POST["Lokasi_l_id"]);

        // Insert the employee data into the database
        $insertQuery = "INSERT INTO daftartunggu (dt_namaPelanggan, dt_jumlahOrang, Lokasi_l_id, Karyawan_k_id)
                        VALUES ('$dt_namaPelanggan', '$dt_jumlahOrang', '$l_id', '$employeeID')";

        if (mysqli_query($db, $insertQuery)) {
            // Employee created successfully, redirect to employee list page
            header("Location: waitlist.php");
            exit();
        } else {
            $error = "Error creating waitlist: " . mysqli_error($db);
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
    <title>Create Waitlist</title>
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
        <h1>Create Waitlist</h1>
        <div class="form-container">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div>
                    <label for="dt_namaPelanggan">Customer Name:</label>
                    <input type="text" id="dt_namaPelanggan" name="dt_namaPelanggan" required>
                </div>

                <div>
                    <label for="dt_jumlahOrang">Number of People:</label>
                    <input type="number" id="dt_jumlahOrang" name="dt_jumlahOrang" required>
                </div>

                <div>
                    <label for="Lokasi_l_id">Location:</label>
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
                    <input type="submit" value="Create Waitlist" class="button">
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
