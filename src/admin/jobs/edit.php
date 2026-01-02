<?php

$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn);

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
    $query = "UPDATE job SET title=:title, brief=:brief WHERE id=:id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
      ":id" => $id,
      ":title" => htmlspecialchars($_POST["title"]),
      ":brief" => htmlspecialchars($_POST["brief"])
    ]);

    header("Location: index.php");
    exit;
}

$title = "Job/$id";
ob_start();
?>

<main>
  <form method="POST">
    <input hidden name="id" value="<?= $job["id"] ?>" />

    <fieldset>
      <legend>Details</legend>

      <label>
        <span>Title</span>
        <input required spellcheck="true" name="title" value="<?= $job["title"] ?>" />
      </label>

      <label>
        <span>Brief</span>
        <textarea required spellcheck="true" name="brief" rows="5"><?= $job["brief"] ?></textarea>
      </label>

    </fieldset>

    <button>Submit</button>
  </form>
</main>

<?php
$content = ob_get_clean();
include($_SERVER["DOCUMENT_ROOT"] . "/admin/layout.php");
