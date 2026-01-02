<?php

$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn);

$id = $_SERVER['REQUEST_METHOD'] == 'GET' ? $_GET["id"] : $_POST["id"];

$query = "SELECT id, title, kind, level FROM skill WHERE id=?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);
$skill = $stmt->fetch();

if (!$skill) {
    http_response_code(404);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $query = "UPDATE skill SET title=:title, kind=:kind, level=:level WHERE id=:id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
      ":id" => $id,
      ":title" => htmlspecialchars($_POST["title"]),
      ":kind" => $_POST["kind"],
      ":level" => intval($_POST["level"])
    ]);

    header("Location: index.php");
    exit;
}

$skill_kinds = ['tool', 'coding', 'hardware', 'science', 'industry', 'language'];

$title = "Skill/$id";
ob_start();
?>

<main>
  <form method="POST">
    <input hidden name="id" value="<?= $skill["id"] ?>" />

    <fieldset>
      <legend>Details</legend>

      <label>
        <span>Title</span>
        <input required spellcheck="true" name="title" value="<?= $skill["title"] ?>" />
      </label>

      <label>
        <span>Kind</span>
        <select name="kind">
          <?php foreach ($skill_kinds as $kind) { ?>
            <option value="<?= $kind ?>" <?= $skill["kind"] === $kind ? "selected" : "" ?>>
              <?= ucfirst($kind) ?>
            </option>
          <?php } ?>
        </select>
      </label>

      <label>
        <span>Level</span>
        <input type="number" required name="level" min="0" max="100" value="<?= $skill["level"] ?>" />
      </label>

    </fieldset>

    <button>Submit</button>
  </form>
</main>

<?php
$content = ob_get_clean();
include($_SERVER["DOCUMENT_ROOT"] . "/admin/layout.php");
