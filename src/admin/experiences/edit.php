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
                  organization.title AS organization
          FROM experience
          LEFT JOIN job ON experience.job=job.id
          LEFT JOIN organization ON experience.organization=organization.id
          WHERE experience.id=?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);

$experience = $stmt->fetch();
if (!$experience) {
    http_response_code(404);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $query = "UPDATE experience 
              SET kind=:kind, title=:title, brief=:brief, details=:details, started=:started, ended=:ended
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
