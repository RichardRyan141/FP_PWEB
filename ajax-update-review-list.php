<?php
    session_start();
    include("config.php");

    $location = isset($_GET['location']) ? $_GET['location'] : 'all';

    function displayReviewData($db, $location) {
        $sql = "SELECT rv.rv_detail, l.l_alamat 
                FROM review rv
                JOIN transaksi t ON t.t_id = rv.Transaksi_t_id
                JOIN lokasi l ON l.l_id = t.Lokasi_l_id"; 
        
        if ($location !== 'all') {
            $sql .= " WHERE l.l_alamat = '$location'";
        }

        $result = mysqli_query($db, $sql);

        if ($result->num_rows > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<div class='review'>";
                echo "<p>Location: {$row['l_alamat']}</p>";
                echo "<p>Review : {$row['rv_detail']}</p>";
                echo "</div>";
            }
        }
        else {
            echo "<p>No reviews available </p>";
        }
    }

    displayReviewData($db, $location);
?>

