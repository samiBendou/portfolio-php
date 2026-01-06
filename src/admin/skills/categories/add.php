<?php

$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $query = "INSERT INTO skill_category(title) VALUES (:title)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
      ":title" => htmlspecialchars($_POST["title"])
    ]);

    header("Location: index.php");
    exit;
}


$title = "Skills/Categories/add";
ob_start();
?>

<main>
  <?php include("form.php") ?>
</main>


<?php
$content = ob_get_clean();
include($_SERVER["DOCUMENT_ROOT"] . "/admin/layout.php");
