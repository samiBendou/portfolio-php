<?php

$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn);

$id = $_SERVER['REQUEST_METHOD'] == 'GET' ? $_GET["id"] : $_POST["id"];

$query = "SELECT id, title, link, logo FROM organization WHERE id=?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);
$organization = $stmt->fetch();

if (!$organization) {
    http_response_code(404);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $query = "UPDATE organization
              SET   title=:title,
                    link=:link
              WHERE id=:id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
      ":id" => $id,
      ":title" => htmlspecialchars($_POST["title"]),
      ":link" => $_POST["link"] ? $_POST["link"] : null
    ]);

    header("Location: index.php");
    exit;
}

$title = "Organization/$id";
ob_start();
?>

<main>
  <form method="POST">
    <input hidden name="id" value="<?= $organization["id"] ?>" />

    <fieldset>
      <legend>Details</legend>

      <label>
        <span>Title</span>
        <input required spellcheck="true" name="title" value="<?= $organization["title"] ?>" />
      </label>

      <label>
        <span>Link</span>
        <input type="url" name="link" value="<?= $organization["link"] ?>" />
      </label>

      <label>
        <span>Logo</span>
        <input type="file" name="logo" value="<?= $organization["logo"] ?>" />
      </label>

    </fieldset>

    <button>Submit</button>
  </form>
</main>

<?php
$content = ob_get_clean();
include($_SERVER["DOCUMENT_ROOT"] . "/admin/layout.php");
