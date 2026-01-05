<?php
$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn);

$id = $_SERVER['REQUEST_METHOD'] == 'GET' ? $_GET["id"] : $_POST["id"];

$query = "SELECT id, title FROM project_category WHERE id=?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);

$category = $stmt->fetch();
if (!$category) {
    http_response_code(404);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $query = "UPDATE project_category SET title=:title WHERE id=:id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
      ":id" => $id,
      ":title" => htmlspecialchars($_POST["title"])
    ]);

    header("Location: index.php");
    exit;
}


$title = "Categories/$id";
ob_start();
?>

<main>
  <?php include("form.php") ?>
</main>

<?php
$content = ob_get_clean();
include($_SERVER["DOCUMENT_ROOT"] . "/admin/layout.php");
