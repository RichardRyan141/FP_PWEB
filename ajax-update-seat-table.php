<?php
    session_start();
    include("config.php");

    $location = isset($_GET['location']) ? $_GET['location'] : 'all';

    // Function to fetch and display employee data
    function displayTableData($db, $location) {
        // Construct the SQL query based on the selected filters
        $sql = "SELECT dm.dm_id, dm.dm_noMeja, sm_statusMeja, l_alamat
                FROM daftarmeja dm
                JOIN statusmeja sm ON dm.StatusMeja_sm_id = sm.sm_id
                JOIN lokasi l ON dm.Lokasi_l_id = l.l_id";

        if ($location !== 'all') {
            $sql .= " WHERE l.l_alamat = '$location'";
        }

        // Execute the query
        $result = mysqli_query($db, $sql);

        $userType = isset($_SESSION['userType']) ? $_SESSION['userType'] : '';

        if ($result->num_rows > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . $row['dm_noMeja'] . "</td>";
                echo "<td>" . $row['sm_statusMeja'] . "</td>";
                echo "<td>" . $row['l_alamat'] . "</td>";
                if ($userType == "Pemilik" || $userType == "Direktur" || $userType == "Manager" || $userType == "Pegawai") {
                    echo "<td>";
                    echo "<a href='edit-table-status.php?id=" . $row['dm_id'] . "'>Edit</a>";
                    if ($userType == "Pemilik")
                    {
                        echo " | <a href='delete-table.php?id=" . $row['dm_id'] . "'>Delete</a>";
                    }
                    echo "</td>";
                }
                
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No table found</td></tr>";
        }
    }

    displayTableData($db, $location);
?>

