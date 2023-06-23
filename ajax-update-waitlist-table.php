<?php
    session_start();
    include("config.php");
    
    $location = isset($_GET['location']) ? $_GET['location'] : 'all';

    // Function to fetch and display employee data
    function displayWaitlistData($db, $location) {
        // Construct the SQL query based on the selected filters
        $sql = "SELECT dt.*, l_alamat
                FROM daftartunggu dt
                JOIN lokasi l ON dt.Lokasi_l_id = l.l_id";

        if ($location !== 'all') {
            $sql .= " WHERE l.l_alamat = '$location'";
        }

        // Execute the query
        $result = mysqli_query($db, $sql);

        $userType = isset($_SESSION['userType']) ? $_SESSION['userType'] : '';
        $counter = 1;
        if ($result->num_rows > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . $counter . "</td>";
                echo "<td>" . $row['dt_namaPelanggan'] . "</td>";
                echo "<td>" . $row['dt_jumlahOrang'] . "</td>";
                echo "<td>" . $row['l_alamat'] . "</td>";
                if ($userType == "Pemilik" || $userType == "Direktur" || $userType == "Manager" || $userType == "Pegawai") {
                    echo "<td>";
                    echo "<a href='edit-waitlist.php?id=" . $row['dt_id'] . "'>Edit</a> | ";
                    echo "<a href='delete-waitlist.php?id=" . $row['dt_id'] . "'>Delete</a>";
                    echo "</td>";
                }
                
                echo "</tr>";
                $counter++;
            }
        } else {
            echo "<tr><td colspan='5'>No waiting list found</td></tr>";
        }
    }

    displayWaitlistData($db, $location);
?>