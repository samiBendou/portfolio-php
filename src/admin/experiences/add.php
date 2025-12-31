<?php

$db_path = $_ENV["DB_URL"];
$pdo = new PDO($db_path);

$title = 'Add experience';
ob_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $query = "INSERT INTO experience(kind, title, brief, details, keypoints, started, ended)
              VALUES (:kind, :title, :brief, :details, {}, :started, :ended)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
      ":kind" => $_POST["kind"],
      ":title" => htmlspecialchars($_POST["title"]),
      ":brief" => htmlspecialchars($_POST["brief"]),
      ":details" => $_POST["details"],
      ":started" => $_POST["started"],
      ":ended" => $_POST["ended"] ? $_POST["ended"] : null,
    ]);

    header("Location: index.php");
    exit;
}

?>

<main>
  <h1>
    <?= $title ?>
  </h1>

  <form method="POST">
    <fieldset>
      <legend>General infos</legend>

      <label>
        <span>Kind</span>
        <select name="kind">
          <option value="internship">Intership</option>
          <option value="job">Job</option>
          <option value="education">Education</option>
        </select>
      </label>

      <label>
        <span>Title</span>
        <input required spellcheck="true" name="title" />
      </label>

      <label>
        <span>Brief</span>
        <textarea name="brief" spellcheck="true" rows="2"></textarea>
      </label>

      <label>
        <span>Details</span>
        <textarea name="details" spellcheck="true" rows="16"></textarea>
      </label>

    </fieldset>

    <fieldset>
      <legend>Dates</legend>

      <fieldset class="inline-field">
        <label>
          <span>From</span>
          <input required type="date" name="started" />
        </label>

        <label>
          <span>To</span>
          <input type="date" name="ended" />
        </label>
      </fieldset>
    </fieldset>

    <button>Submit</button>
  </form>
</main>


<?php
$content = ob_get_clean();
include($_SERVER["DOCUMENT_ROOT"] . "/admin/layout.php");
