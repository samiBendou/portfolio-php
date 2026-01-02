<?php

require_once("consts.php");

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

$skills_levels = [1 => "Beginner", 2 => "Intermediate", 3 => "Advanced", 4 => "Expert"];

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
          <?php foreach (SKILL_KIND as $kind => $label) { ?>
            <option value="<?= $kind ?>" <?= $skill["kind"] === $kind ? "selected" : "" ?>>
              <?= $label ?>
            </option>
          <?php } ?>
        </select>
      </label>


      <label>
        <span>Level</span>
        <select name="level">
          <?php foreach (SKILL_LEVEL as $level => $label) { ?>
            <option value="<?= $level ?>" <?= $skill["level"] === $level ? "selected" : "" ?>>
              <?= $label ?>
            </option>
          <?php } ?>
        </select>
      </label>
    </fieldset>

    <button>Submit</button>
  </form>
</main>

<?php
$content = ob_get_clean();
include($_SERVER["DOCUMENT_ROOT"] . "/admin/layout.php");
