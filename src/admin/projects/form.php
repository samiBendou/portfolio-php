<?php
include($_SERVER["DOCUMENT_ROOT"] . "/admin/skills/consts.php");

if (!isset($project)) {
    $project = [];
}

$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn);

$query = "SELECT id, title FROM experience ORDER BY title ASC";
$experiences = $pdo->query($query);

$query = "SELECT id, title FROM project_category ORDER BY title ASC";
$categories = $pdo->query($query);

$query = "SELECT id, title FROM skill_category ORDER BY title ASC";
$skill_categories = $pdo->query($query)->fetchAll();

$query = "SELECT id, title FROM skill ORDER BY title ASC";
$skills = $pdo->query($query);

$project_skills = [];
if (isset($project)) {
    $query = "SELECT skill FROM project_skill WHERE project=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$project["id"]]);
    $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $project_skills = $result;
}
?>

<form method="POST">
  <input hidden name="id" value="<?= $project["id"] ?>" />

  <fieldset>
    <legend>General infos</legend>
    <label>
      <span>Categories</span>
      <select name="category_id" value="<?= $project["category_id"] ?>">
        <?php foreach ($categories as $category) {
            ?>
        <option value="<?= $category["id"] ?>"
          <?= $project["category_id"] === $category["id"] ? "selected" : "" ?> >
          <?= $category["title"] ?>
        </option>
        <?php
        }
?>
      </select>
    </label>

    <label>
      <span>Title</span>
      <input required spellcheck="true" name="title" value="<?= $project["title"] ?>" />
    </label>

    <label>
      <span>Brief</span>
      <textarea name="brief" spellcheck="true" rows="2"><?= $project["brief"] ?></textarea>
    </label>

    <label>
      <span>Link</span>
      <input type="url" spellcheck="true" name="link" value="<?= $project["link"] ?>" />
    </label>

    <label>
      <span>Details</span>
      <div id="editor-container">
        <div id="editor">
          <?= $project["details"] ?>
        </div>
      </div>
      <textarea name="details" id="details-input" style="display:none;">
        <?= $project["details"] ?>
      </textarea>
    </label>

  </fieldset>

  <fieldset>
    <legend>Dates</legend>
    <fieldset class="inline-field">
      <label>
        <span>From</span>
        <input required type="date" name="started" value="<?= $project["started"] ?>" />
      </label>

      <label>
        <span>To</span>
        <input type="date" name="ended" value="<?= $project["ended"] ?>"/>
      </label>
    </fieldset>
  </fieldset>

  <label>
    <span>Experience</span>
    <select name="experience_id" value="<?= $project["experience_id"] ?>">
      <option value="">None</option>
      <?php foreach ($experiences as $experience) {
          ?>
      <option value="<?= $experience["id"] ?>"
        <?= $project["experience_id"] === $experience["id"] ? "selected" : "" ?> >
        <?= $experience["title"] ?>
      </option>
      <?php
      }
?>
    </select>
  </label>

  <fieldset class="inline-field multi-select-and-new">
    <legend>Skills</legend>

    <fieldset>
      <?php foreach (range(0, 4) as $_) {
          ?>
      <fieldset class="inline-field">
        <label>
          <span>Title</span>
          <input name="skill_title[]" />
        </label>
        <label>
          <span>Category</span>
          <select name="skill_category[]">
            <?php foreach ($skill_categories as $category) { ?>
            <option value="<?= $category["id"] ?>">
              <?= $category["title"] ?>
            </option>
            <?php } ?>
          </select>
        </label>
        <label>
          <span>Level</span>
          <select name="skill_level[]">
            <?php foreach (SKILL_LEVEL as $level => $label) { ?>
            <option value="<?= $level ?>">
              <?= $label ?>
            </option>
            <?php } ?>
          </select>
        </label>
      </fieldset>
      <?php
      }
?>
    </fieldset>

    <label>
      <span>Existing</span>
      <select name="skill_id[]" multiple>
        <?php foreach ($skills as $skill) {
            ?>
        <option value="<?= $skill["id"] ?>"
          <?= in_array($skill["id"], $project_skills) ? "selected" : "" ?> >
          <?= $skill["title"] ?>
        </option>
        <?php
        }
?>
      </select>
    </label>
  </fieldset>

  <button>Submit</button>
</form>

<script>
  const quill = new Quill('#editor', {
    modules: {
      toolbar: [
        ['bold', 'italic', 'underline', 'strike'],
        [{'list': 'ordered'}, {'list': 'bullet'}],
        [{'indent': '-1'}, {'indent': '+1'}],
        ['link'],
        ['clean']
      ]
    }
  });

  const form = document.querySelector('form');
  const details = document.getElementById('details-input');

  form.addEventListener('submit', function () {
    details.value = quill.getSemanticHTML().replaceAll("&nbsp;", " ");
  });
</script>
