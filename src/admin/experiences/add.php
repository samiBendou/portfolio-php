<?php

$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $query = "INSERT INTO experience(kind, title, brief, details, started, ended)
              VALUES (:kind, :title, :brief, :details, :started, :ended)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
      ":kind" => $_POST["kind"],
      ":title" => htmlspecialchars($_POST["title"]),
      ":brief" => htmlspecialchars($_POST["brief"]),
      ":details" => $_POST["details"],
      ":started" => $_POST["started"],
      ":ended" => $_POST["ended"] ? $_POST["ended"] : null,
    ]);

    header("Location: index.php");
    exit;
}


$title = 'Add experience';
ob_start();
?>

<main>
  <h1>
    <?= $title ?>
  </h1>

  <?php include("form.php") ?>
</main>


<?php
$content = ob_get_clean();
include($_SERVER["DOCUMENT_ROOT"] . "/admin/layout.php");
