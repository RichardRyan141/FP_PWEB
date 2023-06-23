<?php
    session_start();
    include("config.php");

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
                    echo "<a href='delete-employee.php?id=" . $row['k_id'] . "' '>Delete</a>";
                    echo "</td>";
                }
                echo "</tr>";
                $counter++;
            }
        } else {
            echo "<tr><td colspan='4'>No employees found</td></tr>";
        }
    }

    // Display employee data based on the selected filters
    displayEmployeeData($db, $location, $position);
?>

