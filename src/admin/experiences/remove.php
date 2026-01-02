<?php

$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn);

$id = $_SERVER['REQUEST_METHOD'] == 'GET' ? $_GET["id"] : $_POST["id"];

$query = "SELECT id, title FROM experience WHERE experience.id=?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);

$experience = $stmt->fetch();
if (!$experience) {
    http_response_code(404);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $query = "DELETE FROM experience WHERE id=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);

    header("Location: index.php");
    exit;
}


$title = 'Remove experience';
ob_start();
?>

<main>
  <h1>
    <?= $title ?>
  </h1>

  <p>Are you sure you want to delete <?= $experience["title"] ?> ?</p>

  <form method="POST">
      <input hidden name="id" value="<?= $experience["id"] ?>" />
      <button>Remove</button> 
  </form>
</main>

<?php
$content = ob_get_clean();
include($_SERVER["DOCUMENT_ROOT"] . "/admin/layout.php");
