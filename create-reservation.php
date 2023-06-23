    <?php
    session_start();
    include("config.php");

    // Check if the user is logged in as Pelanggan
    if (!isset($_SESSION['userType']) || $_SESSION['userType'] != "Pelanggan") {
        header("location: index.php");
        exit();
    }

    $userType = isset($_SESSION['userType']) ? $_SESSION['userType'] : '';
    $username = $_SESSION['login_user'];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $waktu = validateInput($_POST["datetime"]);
        $idLokasi = validateInput($_POST["address"]);
        $noMeja = validateInput($_POST["table"]);
        $hargaTotal = validateInput($_POST["total_price"]);
        $catatan = validateInput($_POST["notes"]);
        $promoID = validateInput($_POST["promo"]);
        $menuIds = json_decode($_POST["menu_ids"]);
        $quantities = json_decode($_POST["quantities"]);

        $sql = "SELECT pe_id FROM pelanggan WHERE pe_nama='$username'";
        $result = mysqli_query($db, $sql);
        $row = mysqli_fetch_assoc($result);
        $userID = $row['pe_id'];

        $sql = "SELECT k_id FROM karyawan WHERE HakAkses_ha_id = 1 AND Lokasi_l_id = '$idLokasi'";
        $result = mysqli_query($db, $sql);
        $row = mysqli_fetch_assoc($result);
        $ownerID = $row['k_id'];

        // Insert data into "transaksi" table
        $transaksiQuery = "INSERT INTO transaksi (t_waktu, t_keterangan, t_hargaTotal, Lokasi_l_id, DaftarMeja_dm_id, StatusTransaksi_st_id, Promosi_pr_id, Pelanggan_pe_id, Karyawan_k_id) 
                           VALUES ('$waktu', '$catatan', '$hargaTotal', '$idLokasi', '$noMeja', 1, '$promoID', '$userID', '$ownerID')";
        mysqli_query($db, $transaksiQuery);

        // Get the last inserted transaction ID
        $lastTransactionId = mysqli_insert_id($db);

        // Insert data into "menupesanan" table for each selected menu
        for ($i = 0; $i < count($menuIds); $i++) {
            $menuId = $menuIds[$i];
            $quantity = $quantities[$i];

            $menupesananQuery = "INSERT INTO menupesanan (mp_jumlahPesanan, Transaksi_t_id, Menu_m_id) 
                                 VALUES ('$quantity', '$lastTransactionId', '$menuId')";
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
    <title>Create Reservation</title>
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
        <h1>Create Reservation</h1>
        <div class="form-container">
            <form method="POST" action="">
                <label for="address">Address:</label>
                <select name="address" id="address">
                    <?php
                        $locationsQuery = "SELECT * FROM lokasi";
                        $locationsResult = mysqli_query($db, $locationsQuery);

                        // Display locations as options in the dropdown
                        while ($location = mysqli_fetch_assoc($locationsResult)) {
                            echo "<option value='" . $location['l_id'] . "'>" . $location['l_alamat'] . "</option>";
                        }
                    ?>
                </select>
                
                <label for="table">Table Number:</label>
                <select name="table" id="table">
                    <?php
                        $tableQuery = "SELECT * FROM daftarmeja";
                        $tableResult = mysqli_query($db, $tableQuery);

                        // Display locations as options in the dropdown
                        while ($table = mysqli_fetch_assoc($tableResult)) {
                            echo "<option value='" . $table['dm_id'] . "'>" . $table['dm_noMeja'] ."</option>";
                        }
                    ?>
                    <!-- Table numbers will be populated dynamically based on the selected address -->
                </select>
                
                <label for="datetime">Date and Time:</label>
                <input type="datetime-local" name="datetime" id="datetime">
                
                <label for="total_price">Total Price:</label>
                <input type="text" name="total_price" id="total_price" readonly value="0">
                
                <label for="notes">Extra Notes:</label>
                <textarea name="notes" id="notes"></textarea>
                
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
                        <!-- Selected menus will be populated dynamically -->
                    </tbody>
                </table>
                
                <label for="menu">Menu:</label>
                <select name="menu" id="menu">
                    <?php
                        $menuQuery = "SELECT * FROM menu";
                        $menuResult = mysqli_query($db, $menuQuery);
                        
                        while ($menu = mysqli_fetch_assoc($menuResult)) {
                            echo '<option value="' . $menu['m_id'] . '">' . $menu['m_nama'] . ' - ' . $menu['m_harga'] . '</option>';
                        }
                    ?>
                </select>
                
                <label for="quantity">Quantity:</label>
                <input type="number" name="quantity" id="quantity" min="1" value="1">
                
                <button type="button" onclick="addMenu()">Add Menu</button>

                <input type="hidden" name="menu_ids" id="menu_ids" value="">
                <input type="hidden" name="quantities" id="quantities" value="">

                <label for="promo">Promo:</label>
                <select name="promo" id="promo">
                    <?php
                        $promoQuery = "SELECT * FROM promosi";
                        $promoResult = mysqli_query($db, $promoQuery);
                        
                        while ($promo = mysqli_fetch_assoc($promoResult)) {
                            echo '<option value="' . $promo['pr_id'] . '">' . $promo['pr_detail'] . ' - ' . $promo['pr_persenDiskon'] . '% s/d Rp ' . $promo['pr_maxDiskon'] . '</option>';
                        }
                    ?>
                </select>

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

