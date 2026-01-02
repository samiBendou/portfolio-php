<?php

$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $query = "INSERT INTO location(country, zip) 
              VALUES (:country, :zip) 
              ON CONFLICT (country, zip) DO UPDATE SET country=:country
              RETURNING id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
      ":country" => $_POST["country"],
      ":zip" => $_POST["zip"],
    ]);
    $location_id = $stmt->fetchColumn();


    $query = "INSERT INTO experience(kind, title, brief, details, started, ended, location)
              VALUES (:kind, :title, :brief, :details, :started, :ended, location)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
      ":kind" => $_POST["kind"],
      ":title" => htmlspecialchars($_POST["title"]),
      ":brief" => htmlspecialchars($_POST["brief"]),
      ":details" => $_POST["details"],
      ":started" => $_POST["started"],
      ":ended" => $_POST["ended"] ? $_POST["ended"] : null,
      ":location" => $_POST["location_id"]
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
