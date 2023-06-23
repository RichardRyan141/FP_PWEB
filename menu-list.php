<?php
    // Include the necessary files and start the session
    session_start();
    include("config.php");

    $userType = isset($_SESSION['userType']) ? $_SESSION['userType'] : '';

    // Retrieve the menu items from the database
    $sql = "SELECT * FROM menu";
    $result = mysqli_query($db, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Restaurant Website - Menu List</title>
    <style>
        body {
            margin: 0;
        }
        
        .top-bar {
            background-color: yellow;
            padding: 10px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }
        
        .top-bar img {
            max-width: 35px;
            margin-right: 20px;
        }

        .top-bar a {
            margin-right: 10px;
            margin-left: 10px;
        }

        .menu-bar {
            background-color: lightgray;
            padding: 10px;
            width: 200px;
            transition: width 0.3s ease;
            position: fixed;
            top: 0;
            bottom: 0;
        }
        
        .menu-bar.collapsed {
            width: 20px; /* Adjust the width according to your needs */
        }
        
        .menu-bar ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
        }
        
        .menu-bar ul li {
            padding: 10px;
            transition: opacity 0.3s ease;
        }
        
        /* Hide the menu items when collapsed */
        .menu-bar.collapsed ul li {
            opacity: 0;
        }
        
        .menu-toggle {
            display: inline-block;
            width: 30px;
            height: 20px;
            cursor: pointer;
            position: relative;
            z-index: 999;
        }
        
        .menu-toggle .bar {
            background-color: black;
            height: 3px;
            margin-bottom: 6px;
            transition: background-color 0.3s ease;
        }
                
        /* Style for the collapsed menu bar */
        .collapsed-menu-bar {
            background-color: lightgray;
            width: 40px; /* Adjust the width according to your needs */
            transition: width 0.3s ease;
        }
        
        .collapsed-menu-bar.collapsed {
            width: 0;
            overflow: hidden;
        }
        
        h1 {
            margin-left: 250px; /* Adjust the margin according to the menu bar width */
            padding: 10px;
        }
        
        .content {
            margin-left: 250px; /* Adjust the margin according to the menu bar width */
            padding: 10px;
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
        }

        .menu-card-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .menu-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 200px;
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }

        .menu-card img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .menu-card .card-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .menu-card .name {
            margin-bottom: 10px;
            font-size: 16px;
            font-weight: bold;
        }

        .menu-card .description {
            margin-bottom: 10px;
        }

        .menu-card .price {
            font-weight: bold;
        }

        .menu-card a {
            margin-top: 10px;
        }

        .button {
            display: block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            width: 100%;
            text-align: center;
        }

    </style>
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
    <h1>Menu List</h1>
    <div class="content">
        <div class="menu-card-container">
            <?php
            // Display each menu item as a card
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<div class='menu-card'>";
                echo "<img src='" . $row['m_foto'] . "' alt='Menu Image'>";
                echo "<div class='name'>" . $row['m_nama'] . "</div>";
                echo "<div class='description'>" . $row['m_deskripsi'] . "</div>";
                echo "<div class='price'>Rp " . $row['m_harga'] . "</div>";
                if ($userType == "Pemilik") {
                    echo "<a href='edit-menu.php?id=" . $row['m_id'] . "'>Edit</a>";
                    echo "<br>";
                    echo "<a href='delete-menu.php?id=" . $row['m_id'] . "'>Delete</a>";
                }
                echo "</div>";
            }
            ?>
        </div>
        <?php
        if ($userType == "Pemilik") {
            echo "<a href='create-menu.php' class='button'>Create New Menu</a>";
        }    
        ?>
    </div>
</body>
</html>
