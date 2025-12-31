<?php
$title = "Home";
ob_start();
?>

<main>
  <h2>Just navigate bro!</h2>
</main>

<?php
$content = ob_get_clean();
include("layout.php");
