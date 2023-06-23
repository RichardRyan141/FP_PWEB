<?php
    session_start();
    include("config.php");
    $userType = isset($_SESSION['userType']) ? $_SESSION['userType'] : '';
    $username = $_SESSION['login_user'];

    if ((!isset($_GET['id']))) {
        header("Location: reservation-list.php");
    }

    $reservationID = $_GET['id'];

    $reservationQuery = "SELECT * FROM transaksi WHERE t_id = '$reservationID'";
    $reservationResult = mysqli_query($db, $reservationQuery);
    $reservation = mysqli_fetch_assoc($reservationResult);

    $locationsQuery = "SELECT l.* 
                       FROM lokasi l 
                       JOIN transaksi t ON l.l_id = t.Lokasi_l_id
                       WHERE t_id = '$reservationID'";
    $locationsResult = mysqli_query($db, $locationsQuery);
    $location = mysqli_fetch_assoc($locationsResult);
    $locID = $location['l_id'];
    
    $ordersQuery = "SELECT * FROM menupesanan WHERE Transaksi_t_id = '$reservationID'";
    $ordersResult = mysqli_query($db, $ordersQuery);
    $menuIDs = array();
    $quantities = array();
    while ($order = mysqli_fetch_assoc($ordersResult)) {
        $menuID = $order['Menu_m_id'];
        $quantity = $order['mp_jumlahPesanan'];

        // Add the menu ID and quantity to their respective arrays
        $menuIDs[] = $menuID;
        $quantities[] = $quantity;
    }


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $waktu = validateInput($_POST["datetime"]);
        $idLokasi = validateInput($_POST["address"]);
        $noMeja = validateInput($_POST["table"]);
        $hargaTotal = validateInput($_POST["total_price"]);
        $catatan = validateInput($_POST["notes"]);
        $promoID = validateInput($_POST["promo"]);
        $menuIds = json_decode($_POST["menu_ids"]);
        $quantities = json_decode($_POST["quantities"]);

        $sql = "UPDATE transaksi
                SET t_waktu = '$waktu', t_keterangan = '$catatan', t_hargaTotal = '$hargaTotal', Lokasi_l_id = '$idLokasi', DaftarMeja_dm_id = '$noMeja', Promosi_pr_id = '$promoID'
                WHERE t_id = '$reservationID'";
        mysqli_query($db, $sql);

        if ($userType != "Pelanggan") {
            $statusID = validateInput($_POST["st_id"]);
            $sql = "UPDATE transaksi SET StatusTransaksi_st_id = '$statusID' WHERE t_id = '$reservationID'";
            mysqli_query($db, $sql);
        }

        // Insert data into "menupesanan" table for each selected menu
        for ($i = 0; $i < count($menuIds); $i++) {
            $menuId = $menuIds[$i];
            $quantity = $quantities[$i];

            $menupesananQuery = "INSERT INTO menupesanan (mp_jumlahPesanan, Transaksi_t_id, Menu_m_id) 
                                 VALUES ('$quantity', '$reservationID', '$menuId')";
            mysqli_query($db, $menupesananQuery);
        }

        // Redirect to a success page or perform any other desired action
        header("Location: reservation-list.php");
        exit();
    }

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
    <title>Edit Reservation</title>
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
        <h1>Edit Reservation</h1>
        <div class="form-container">
            <form method="POST" action="">
                <label for="address">Address:</label>
                <select name="address" id="address">
                    <?php
                        echo "<option value='" . $location['l_id'] . "'>" . $location['l_alamat'] . "</option>";
                    ?>
                </select>
                
                <label for="table">Table Number:</label>
                <select name="table" id="table">
                    <?php
                        $tableQuery = "SELECT * 
                                       FROM daftarmeja
                                       WHERE Lokasi_l_id = '$locID'";
                        $tableResult = mysqli_query($db, $tableQuery);

                        // Display locations as options in the dropdown
                        while ($table = mysqli_fetch_assoc($tableResult)) {
                            echo "<option value='" . $table['dm_id'] . "'>" . $table['dm_noMeja'] ."</option>";
                        }
                    ?>
                </select>
                
                <label for="datetime">Date and Time:</label>
                <?php
                    $waktuReservasi = $reservation['t_waktu'];
                    if ($userType == "Pelanggan") {
                        echo "<input type='datetime-local' name='datetime' id='datetime' value='$waktuReservasi'>";
                    } else {
                        echo "<input type='datetime-local' name='datetime' id='datetime' value='$waktuReservasi' readonly>";
                    }
                ?>
                <label for="total_price">Total Price:</label>
                <input type="text" name="total_price" id="total_price" readonly value="<?php echo $reservation['t_hargaTotal']; ?>">
                
                <label for="notes">Extra Notes:</label>
                <textarea name="notes" id="notes"><?php echo $reservation['t_keterangan']; ?></textarea>
                
                <h3>Menu Selection</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Menu Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="menu_table">
                        <?php
                            for ($i = 0; $i < count($menuIDs); $i++) {
                                // Retrieve menu information based on the menu ID
                                $menuID = $menuIDs[$i];
                                $menuQty = $quantities[$i];
                                $menuQuery = "SELECT * FROM menu WHERE m_id = '$menuID'";
                                $menuResult = mysqli_query($db, $menuQuery);
                                $menu = mysqli_fetch_assoc($menuResult);
                                echo "<tr>";
                                echo "<td>" . $menu['m_nama'] . "</td>";
                                echo "<td>" . $menu['m_harga'] . "</td>";
                                echo "<td>" . $menuQty . "</td>";
                                echo "<td><button onclick=\"deleteMenu($i)\">Delete</button></td>";
                                echo "<tr>";
                            }
                        ?>
                    </tbody>
                </table>
                
                <?php
                    if ($userType == "Pelanggan") {
                        echo "<label for='menu'>Menu:</label>";
                        echo "<select name='menu' id='menu'>";
                                $menuQuery = "SELECT * FROM menu";
                                $menuResult = mysqli_query($db, $menuQuery);
                                
                                while ($menu = mysqli_fetch_assoc($menuResult)) {
                                    echo '<option value="' . $menu['m_id'] . '">' . $menu['m_nama'] . ' - ' . $menu['m_harga'] . '</option>';
                                }
                        echo "</select>";
                        
                        echo "<label for='quantity'>Quantity:</label>";
                        echo "<input type='number' name='quantity' id='quantity' min='1' value='1'>";
                        
                        echo "<button type='button' onclick='addMenu()'>Add Menu</button>";
                    }
                ?>
                <input type="hidden" name="menu_ids" id="menu_ids" value="">
                <input type="hidden" name="quantities" id="quantities" value="">

                <label for="promo">Promo:</label>
                <select name="promo" id="promo">
                    <?php
                        if ($userType == "Pelanggan") {
                            $promoQuery = "SELECT * FROM promosi";
                            $promoResult = mysqli_query($db, $promoQuery);
                            
                            while ($promo = mysqli_fetch_assoc($promoResult)) {
                                echo '<option value="' . $promo['pr_id'] . '">' . $promo['pr_detail'] . ' - ' . $promo['pr_persenDiskon'] . '% s/d Rp ' . $promo['pr_maxDiskon'] . '</option>';
                            }
                        } else {
                            $sql = "SELECT Promosi_pr_id FROM transaksi WHERE t_id = '$reservationID'";
                            $result = mysqli_query($db, $sql);
                            $promo = mysqli_fetch_assoc($result);
                            $promoID = $promo['Promosi_pr_id'];

                            $sql = "SELECT * FROM promosi WHERE pr_id = '$promoID'";
                            $result = mysqli_query($db, $sql);
                            $promo = mysqli_fetch_assoc($result);
                            echo '<option value="' . $promo['pr_id'] . '">' . $promo['pr_detail'] . ' - ' . $promo['pr_persenDiskon'] . '% s/d Rp ' . $promo['pr_maxDiskon'] . '</option>';
                        }  
                    ?>
                </select>

                <?php
                    if ($userType != "Pelanggan") {
                        echo "<label for='st_id'>Status Transaksi:</label>";
                        echo "<select name='st_id' id='st_id'>";
                            $statusQuery = "SELECT * FROM statustransaksi";
                            $statusResult = mysqli_query($db, $statusQuery);
                            
                            while ($status = mysqli_fetch_assoc($statusResult)) {
                                echo '<option value="' . $status['st_id'] . '">' . $status['st_status'] . '</option>';
                            }
                        echo "</select>";
                    }
                ?>
                <br>
                <input type="submit" name="confirm_order" value="Confirm Order" class="button" onclick="confirmOrder()">
            </form>
        </div>
    </div>

    <script>
        // Define arrays to store menu IDs and quantities
        var menuIds = [];
        var quantities = [];

        function addMenu() {
            // Get the selected menu ID and quantity
            var menuId = document.getElementById("menu").value;
            var quantity = document.getElementById("quantity").value;

            // Add the menu ID and quantity to their respective arrays
            menuIds.push(menuId);
            quantities.push(quantity);

            for (var i = 0; i < menuIds.length; i++) {
                var menu_id = menuIds[i];
                var qty = quantities[i];
                console.log("Menu id : ", menu_id);
                console.log("Qty : ", qty);
            }
            // Update the menu table and total price
            updateMenuTable();
            updateTotalPrice();
        }

        function createDeleteFunction(index) {
            return function() {
                // Remove the menu and quantity at the given index from the arrays
                menuIds.splice(index, 1);
                quantities.splice(index, 1);

                // Update the menu table and total price
                updateMenuTable();
                updateTotalPrice();
            };
        }

        function updateTotalPrice() {
            var totalPrice = 0;
            var promoValue = 0;

            // Get the selected promotion details
            var promoDropdown = document.getElementById("promo");
            var selectedPromoOption = promoDropdown.options[promoDropdown.selectedIndex];
            var promoDiscount = parseFloat(selectedPromoOption.text.split(" - ")[1].split("%")[0]);
            var promoMaxDiscount = parseFloat(selectedPromoOption.text.split(" s/d Rp ")[1]);

            // Calculate the total price based on the selected menus and quantities
            for (var i = 0; i < menuIds.length; i++) {
                var menuId = menuIds[i];
                var quantity = quantities[i];

                // Get the menu details from the dropdown options
                var menuDropdown = document.getElementById("menu");
                var selectedOption = menuDropdown.options[menuDropdown.selectedIndex];
                var menuPrice = parseFloat(selectedOption.text.split(" - ")[1]);

                // Calculate the subtotal for the menu
                var subtotal = menuPrice * quantity;
                totalPrice += subtotal;
            }

            // Calculate the discount based on the promotion
            if (promoDiscount > 0) {
                var discount = totalPrice * (promoDiscount / 100);
                promoValue = Math.min(discount, promoMaxDiscount);
            }

            // Update the total price textfield
            document.getElementById("total_price").value = (totalPrice-promoValue).toFixed(2);
        }

        function updateMenuTable() {
            var menuTableBody = document.getElementById("menu_table");

            // Clear the menu table
            menuTableBody.innerHTML = "";

            // Populate the menu table with selected menus
            for (var i = 0; i < menuIds.length; i++) {
                var menuId = menuIds[i];
                var quantity = quantities[i];

                // Get the menu details from the dropdown options
                var menuDropdown = document.getElementById("menu");
                var selectedOption = menuDropdown.options[menuDropdown.selectedIndex];
                var menuName = selectedOption.text.split(" - ")[0];
                var menuPrice = selectedOption.text.split(" - ")[1];

                // Create a new row in the table
                var row = menuTableBody.insertRow();

                // Insert cells for menu name, price, quantity, and action
                var menuNameCell = row.insertCell(0);
                var menuPriceCell = row.insertCell(1);
                var quantityCell = row.insertCell(2);
                var actionCell = row.insertCell(3);

                // Set the cell values
                menuNameCell.innerHTML = menuName;
                menuPriceCell.innerHTML = menuPrice;
                quantityCell.innerHTML = quantity;

                // Create a delete button for removing the menu
                var deleteButton = document.createElement("button");
                deleteButton.innerHTML = "Delete";
                deleteButton.addEventListener("click", createDeleteFunction(i));

                // Append the delete button to the action cell
                actionCell.appendChild(deleteButton);
            }
        }

        function confirmOrder() {
            // Set the value of the hidden input fields to the arrays
            document.getElementById("menu_ids").value = JSON.stringify(menuIds);
            document.getElementById("quantities").value = JSON.stringify(quantities);

            // Submit the form
            document.forms[0].submit();
        }

    </script>
</body>
</html>

