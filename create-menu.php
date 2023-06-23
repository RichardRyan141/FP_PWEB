<?php
    // Include the necessary files and start the session
    session_start();
    include("config.php");

    // Check if the user is logged in and has the necessary permissions
    if (!isset($_SESSION['login_user']) || $_SESSION['userType'] !== "Pemilik") {
        header("location: menu-list.php");
        exit();
    }

    $userType = isset($_SESSION['userType']) ? $_SESSION['userType'] : '';

    // Define variables and initialize with empty values
    $name = $description = $price = $kategori = "";
    $name_err = $description_err = $price_err = $kategori_err = $photo_err = "";

    // Processing form data when the form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate name
        if (empty(trim($_POST["name"]))) {
            $name_err = "Please enter the menu name.";
        } else {
            $name = trim($_POST["name"]);
        }

        // Validate description
        if (empty(trim($_POST["description"]))) {
            $description_err = "Please enter the menu description.";
        } else {
            $description = trim($_POST["description"]);
        }

        // Validate price
        if (empty(trim($_POST["price"]))) {
            $price_err = "Please enter the menu price.";
        } elseif (!is_numeric(trim($_POST["price"]))) {
            $price_err = "Price must be a numeric value.";
        } else {
            $price = trim($_POST["price"]);
        }

        // Validate kategori
        if (empty(trim($_POST["kategori"]))) {
            $kategori_err = "Please select the menu category.";
        } else {
            $kategori = trim($_POST["kategori"]);
        }

        // Validate photo
        $target_dir = "images/";
        $target_file = $target_dir . basename($_FILES["photo"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        if (isset($_POST["submit"])) {
            $check = getimagesize($_FILES["photo"]["tmp_name"]);
            if ($check !== false) {
                $uploadOk = 1;
            } else {
                $photo_err = "File is not an image.";
                $uploadOk = 0;
            }
        }

        // Check file size
        if ($_FILES["photo"]["size"] > 500000) {
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
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
                // File uploaded successfully
            } else {
                $photo_err = "Sorry, there was an error uploading your file.";
            }
        }

        // Check input errors before inserting into the database
        if (empty($name_err) && empty($description_err) && empty($price_err) && empty($kategori_err) && empty($photo_err)) {
            // Prepare the insert statement
            $sql = "INSERT INTO menu (m_nama, m_deskripsi, m_harga, m_kategori, m_foto) VALUES (?, ?, ?, ?, ?)";

            if ($stmt = mysqli_prepare($db, $sql)) {
                // Bind the parameters with the prepared statement
                mysqli_stmt_bind_param($stmt, "sssbs", $param_name, $param_description, $param_price, $param_kategori, $param_photo);

                // Set the parameters
                $param_name = $name;
                $param_description = $description;
                $param_price = $price;
                $param_kategori = $kategori;
                $param_photo = $target_file;

                // Execute the prepared statement
                if (mysqli_stmt_execute($stmt)) {
                    // Menu created successfully, redirect to menu list
                    header("location: menu-list.php");
                    exit();
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }

                // Close the statement
                mysqli_stmt_close($stmt);
            }
        }

        // Close the database connection
        mysqli_close($db);
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Menu</title>
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
        <h1>Create Menu</h1>
        <div class="form-container">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
                <div>
                    <label for="name">Menu Name:</label>
                    <input type="text" name="name" id="name" value="<?php echo $name; ?>">
                    <span><?php echo $name_err; ?></span>
                </div>
                <div>
                    <label for="description">Menu Description:</label>
                    <textarea name="description" id="description"><?php echo $description; ?></textarea>
                    <span><?php echo $description_err; ?></span>
                </div>
                <div>
                    <label for="price">Menu Price:</label>
                    <input type="text" name="price" id="price" value="<?php echo $price; ?>">
                    <span><?php echo $price_err; ?></span>
                </div>
                <div>
                    <label for="kategori">Kategori:</label>
                    <select name="kategori" id="kategori">
                        <option value="Makanan">Makanan</option>
                        <option value="Snack">Snack</option>
                        <option value="Minuman">Minuman</option>
                    </select>
                    <span><?php echo $kategori_err; ?></span>
                </div>
                <div>
                    <label for="photo">Menu Photo:</label>
                    <input type="file" name="photo" id="photo">
                    <span><?php echo $photo_err; ?></span>
                </div>
                <div>
                    <input type="submit" value="Create">
                </div>
            </form>
        </div>
    </div>
</body>
</html>
