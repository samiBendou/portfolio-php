<?php

$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn, $_ENV["DB_USER"], $_ENV["DB_PASSWORD"]);

$id = $_SERVER['REQUEST_METHOD'] == 'GET' ? $_GET["id"] : $_POST["id"];

$query = "SELECT id, title, brief FROM job WHERE id=?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);

$job = $stmt->fetch();
if (!$job) {
    http_response_code(404);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $query = "DELETE FROM job WHERE id=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);

    header("Location: index.php");
    exit;
}


$title = "Job/$id";
ob_start();
?>

<main>
  <p>Are you sure you want to remove <?= $job["title"] ?> ?</p>

  <form method="POST">
      <input hidden name="id" value="<?= $job["id"] ?>" />
      <button>Remove</button>
  </form>
</main>

<?php
$content = ob_get_clean();
include($_SERVER["DOCUMENT_ROOT"] . "/admin/layout.php");
