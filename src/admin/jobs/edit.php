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
      ":brief" => $_POST["brief"]
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
        <div id="editor-container">
          <div id="editor"><?= $job["brief"] ?></div>
        </div>
        <textarea name="brief" id="brief-input" style="display:none;"><?= $job["brief"] ?></textarea>
      </label>

    </fieldset>

    <button>Submit</button>
  </form>
</main>

<script>
  const quill = new Quill('#editor', {
    modules: {
      toolbar: [
        ['bold', 'italic', 'underline', 'strike'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'indent': '-1'}, { 'indent': '+1' }],
        ['link'],
        ['clean']
      ]
    }
  });

  const form = document.querySelector('form');
  const details = document.getElementById('brief-input');

  form.addEventListener('submit', function() {
    details.value = quill.getSemanticHTML().replaceAll("&nbsp;", " ");
  });
</script>

<?php
$content = ob_get_clean();
include($_SERVER["DOCUMENT_ROOT"] . "/admin/layout.php");
