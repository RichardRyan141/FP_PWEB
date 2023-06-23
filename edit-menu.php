<?php
    // Include the necessary files and start the session
    session_start();
    include("config.php");

    // Check if user is logged in and has necessary privileges
    if (!isset($_SESSION['login_user']) || $_SESSION['userType'] != 'Pemilik') {
        header("Location: login.php");
        exit();
    }

    // Check if menu ID is provided
    if (!isset($_GET['id'])) {
        header("Location: menu-list.php");
        exit();
    }

    $userType = isset($_SESSION['userType']) ? $_SESSION['userType'] : '';
    $menuId = $_GET['id'];

    // Retrieve the menu item from the database
    $query = "SELECT * FROM menu WHERE m_id = '$menuId'";
    $result = mysqli_query($db, $query);

    if (mysqli_num_rows($result) != 1) {
        // Menu item not found, redirect to menu list page
        header("Location: menu-list.php");
        exit();
    }

    $row = mysqli_fetch_assoc($result);

    // Process the form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Retrieve the form data
        $nama = $_POST['nama'];
        $deskripsi = $_POST['deskripsi'];
        $harga = $_POST['harga'];
        $foto = $_FILES['foto'];

        // Check if a new photo is uploaded
        if (!empty($foto['name'])) {
            $oldPicture = $row['m_foto'];
            if (!empty($oldPicture)) {
                unlink($oldPicture); // Delete the old photo
            }

            // Upload the new photo
            $target_dir = "images/";
            $target_file = $target_dir . basename($_FILES["foto"]["name"]);
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check if image file is a actual image or fake image
            if (isset($_POST["submit"])) {
                $check = getimagesize($_FILES["foto"]["tmp_name"]);
                if ($check !== false) {
                    $uploadOk = 1;
                } else {
                    $photo_err = "File is not an image.";
                    $uploadOk = 0;
                }
            }

            // Check file size
            if ($_FILES["foto"]["size"] > 500000) {
                $photo_err = "Sorry, your file is too large.";
                $uploadOk = 0;
            }

            // Allow only certain file formats
            $allowedExtensions = ["jpg", "jpeg", "png", "gif"];
            if (!in_array($imageFileType, $allowedExtensions)) {
                $photo_err = "Sorry, only JPG, JPEG, PNG, and GIF files are allowed.";
                $uploadOk = 0;
            }

            // Move uploaded file to the server
            if ($uploadOk == 1) {
                if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
                    $sql = "UPDATE menu SET m_nama = '$nama', m_deskripsi = '$deskripsi', m_harga = '$harga', m_foto = '$target_file' WHERE m_id = '$menuId'";
                    $query = mysqli_query($db, $sql);

                    if ($query) {
                        header('Location: menu-list.php');
                    } else {
                        die("Gagal menyimpan perubahan...");
                    }
                } else {
                    $photo_err = "Sorry, there was an error uploading your file.";
                }
            }
        } else {
            // Update the menu item in the database without changing the photo
            $updateQuery = "UPDATE menu SET m_nama = '$nama', m_deskripsi = '$deskripsi', m_harga = '$harga' WHERE m_id = '$menuId'";

            if (mysqli_query($db, $updateQuery)) {
                // Menu item updated successfully, redirect to menu list page
                header("Location: menu-list.php");
                exit();
            } else {
                $error = "Error updating menu item: " . mysqli_error($db);
            }
        }
    }
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
        <?php
            // Display error message if any
            if (isset($error)) {
                echo "<p>Error: $error</p>";
            }
        ?>

        <h1>Edit Menu</h1>

        <div class="form-container">
            <form action="" method="POST" enctype="multipart/form-data">
                <label for="nama">Name:</label>
                <input type="text" name="nama" id="nama" value="<?php echo $row['m_nama']; ?>" required><br><br>

                <label for="deskripsi">Description:</label>
                <textarea name="deskripsi" id="deskripsi" rows="4" cols="50" required><?php echo $row['m_deskripsi']; ?></textarea><br><br>

                <label for="harga">Price:</label>
                <input type="number" name="harga" id="harga" value="<?php echo $row['m_harga']; ?>" required><br><br>

                <label for="photo">Photo:</label>
                <input type="file" name="foto" id="foto"><br><br>

                <input type="submit" value="Update">
            </form>
        </div>

    </div>
</body>
</html>
