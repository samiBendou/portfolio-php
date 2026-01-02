<?php

$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn);

$id = $_SERVER['REQUEST_METHOD'] == 'GET' ? $_GET["id"] : $_POST["id"];

$query = "SELECT  experience.id as id,
                  experience.title AS title,
                  job.title AS job,
                  kind,
                  experience.brief AS brief,
                  details,
                  started,
                  ended,
                  organization.title AS organization,
                  location.country AS country,
                  location.zip AS zip,
                  location.id AS location_id 
          FROM experience
          LEFT JOIN job ON experience.job=job.id
          LEFT JOIN organization ON experience.organization=organization.id
          LEFT JOIN location ON experience.location=location.id
          WHERE experience.id=?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);

$experience = $stmt->fetch();
if (!$experience) {
    http_response_code(404);
    exit;
}

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

    $query = "UPDATE experience 
              SET   kind=:kind, 
                    title=:title, 
                    brief=:brief, 
                    details=:details, 
                    started=:started, 
                    ended=:ended,
                    location=:location
              WHERE id=:id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
      ":id" => $id,
      ":kind" => $_POST["kind"],
      ":title" => htmlspecialchars($_POST["title"]),
      ":brief" => htmlspecialchars($_POST["brief"]),
      ":details" => $_POST["details"],
      ":started" => $_POST["started"],
      ":ended" => $_POST["ended"] ? $_POST["ended"] : null,
      ":location" => $location_id
    ]);

    header("Location: index.php");
    exit;
}


$title = 'Edit experience';
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
