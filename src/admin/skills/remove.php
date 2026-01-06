<?php

$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn);

$id = $_SERVER['REQUEST_METHOD'] == 'GET' ? $_GET["id"] : $_POST["id"];

$query = "SELECT id, title FROM skill WHERE id=?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);
$skill = $stmt->fetch();
if (!$skill) {
    http_response_code(404);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $query = "DELETE FROM skill WHERE id=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);

    header("Location: index.php");
    exit;
}


$title = "Skill/$id";
ob_start();
?>

<main>
  <p>Are you sure you want to remove <?= $skill["title"] ?> ?</p>

  <form method="POST">
      <input hidden name="id" value="<?= $skill["id"] ?>" />
      <button>Remove</button>
  </form>
</main>

<?php
$content = ob_get_clean();
include($_SERVER["DOCUMENT_ROOT"] . "/admin/layout.php");
