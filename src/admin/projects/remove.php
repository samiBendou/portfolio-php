<?php

$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn, $_ENV["DB_USER"], $_ENV["DB_PASSWORD"]);

$id = $_SERVER['REQUEST_METHOD'] == 'GET' ? $_GET["id"] : $_POST["id"];

$query = "SELECT id, title FROM project WHERE project.id=?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);

$project = $stmt->fetch();
if (!$project) {
    http_response_code(404);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $query = "DELETE FROM project WHERE id=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);

    header("Location: index.php");
    exit;
}


$title = "Projects/$id";
ob_start();
?>

<main>
  <p>Are you sure you want to remove <em><?= $project["title"] ?></em> ?</p>

  <form method="POST">
      <input hidden name="id" value="<?= $project["id"] ?>" />
      <button>Remove</button>
  </form>
</main>

<?php
$content = ob_get_clean();
include($_SERVER["DOCUMENT_ROOT"] . "/admin/layout.php");
