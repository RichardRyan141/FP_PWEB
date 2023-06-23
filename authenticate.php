<?php
include("config.php");

if (isset($_POST['login'])) {
  $username = $_POST['username'];
  $password = $_POST['password'];

  $sql = "SELECT * FROM pelanggan WHERE pe_nama = '$username' and pe_password = '$password'";
  $result = mysqli_query($db, $sql);
  $count = mysqli_num_rows($result);

  // If result matched username and password, table row must be 1 row
  if ($count == 1) {
    // Authentication successful
    session_start();
    $_SESSION['login_user'] = $username;
    $_SESSION['userType'] = "Pelanggan";
    header("location: index.php");
  } else {
    $sql = "SELECT ha.ha_namaLevel 
            FROM karyawan k
            JOIN hakakses ha ON k.HakAkses_ha_id = ha.ha_id
            WHERE k_nama = '$username' and k_password = '$password'";
    $result = mysqli_query($db, $sql);
    $count = mysqli_num_rows($result);

    if ($count == 1) {
      $row = mysqli_fetch_assoc($result);
      $accessLevel = $row['ha_namaLevel'];

      session_start();
      $_SESSION['login_user'] = $username;
      $_SESSION['userType'] = $accessLevel;
      header("location: index.php");
    } else {
      header("location: login.php?error=1");
      exit;
    }
  }
}
?>
