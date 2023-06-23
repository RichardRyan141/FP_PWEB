<?php
    session_start();
    include("config.php");

    if (isset($_SESSION['userType'])) {
        header("location: index.php");
        exit();
    }

    // Define variables and set them empty initially
    $pe_nama = $pe_noTelepon = $pe_alamat = $pe_email = $pe_password = $pe_templateEmail = "";
    $error = "";

    // Process the form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $pe_nama = validateInput($_POST["pe_nama"]);
        $pe_noTelepon = validateInput($_POST["pe_noTelepon"]);
        $pe_alamat = validateInput($_POST["pe_alamat"]);
        $pe_email = validateInput($_POST["pe_email"]);
        $pe_password = validateInput($_POST["pe_password"]);
        $pe_templateEmail = "Hi, $pe_nama. Thank you for reserving with us. Following is your reservation detail";

        $insertQuery = "INSERT INTO pelanggan (pe_nama, pe_noTelepon, pe_alamat, pe_email, pe_password, pe_templateEmail)
                        VALUES ('$pe_nama', '$pe_noTelepon', '$pe_alamat', '$pe_email', '$pe_password', '$pe_templateEmail')";

        if (mysqli_query($db, $insertQuery)) {
            $_SESSION['login_user'] = $pe_nama;
            $_SESSION['userType'] = "Pelanggan";
            header("Location: index.php");
            exit();
        } else {
            $error = "Error registering user : " . mysqli_error($db);
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
    <title>Register</title>
    <style>
        body {
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f9f9f9;
        }
        
        .content {
            background-color: #f9f9f9;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="password"] {
            width: 90%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            padding: 8px 15px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin-top: 20px;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: red;
            margin-top: 10px;
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
    <div class="content">
        <h1>Register User</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div>
                <label for="pe_nama">Username *:</label>
                <input type="text" id="pe_nama" name="pe_nama" required>
            </div>

            <div>
                <label for="pe_noTelepon">Phone Number *:</label>
                <input type="text" id="pe_noTelepon" name="pe_noTelepon" required>
            </div>

            <div>
                <label for="pe_alamat">Address:</label>
                <input type="text" id="pe_alamat" name="pe_alamat">
            </div>

            <div>
                <label for="pe_email">Email *:</label>
                <input type="email" id="pe_email" name="pe_email" required>
            </div>

            <div>
                <label for="pe_password">Password *:</label>
                <input type="password" id="pe_password" name="pe_password" required>
            </div>

            <div>
                <input type="submit" value="Register">
            </div>
        </form>
        <p><a href="login.php"> Back to Login Page</a></p>
        <?php
            if ($error) {
                echo "<p>Error: $error</p>";
            }
        ?>

    </div>
</body>
</html>
