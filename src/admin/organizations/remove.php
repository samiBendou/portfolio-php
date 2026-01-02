<?php

$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn);

$id = $_SERVER['REQUEST_METHOD'] == 'GET' ? $_GET["id"] : $_POST["id"];

$query = "SELECT id, title, link, logo FROM organization WHERE organization.id=?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);

$organization = $stmt->fetch();
if (!$organization) {
    http_response_code(404);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $query = "DELETE FROM organization WHERE id=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);

    header("Location: index.php");
    exit;
}


$title = "Organization/$id";
ob_start();
?>

<main>
  <h1>
    <?= $title ?>
  </h1>

  <p>Are you sure you want to remove <?= $organization["title"] ?> ?</p>

  <form method="POST">
      <input hidden name="id" value="<?= $organization["id"] ?>" />
      <button>Remove</button> 
  </form>
</main>

<?php
$content = ob_get_clean();
include($_SERVER["DOCUMENT_ROOT"] . "/admin/layout.php");
