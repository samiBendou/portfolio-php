<?php
include($_SERVER["DOCUMENT_ROOT"] . "/admin/skills/consts.php");
include("consts.php");

if (!isset($experience)) {
    $experience = [];
}

$kind = $experience["kind"];


$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn, $_ENV["DB_USER"], $_ENV["DB_PASSWORD"]);

$query = "SELECT id, title FROM organization ORDER BY title ASC";
$organizations = $pdo->query($query);

$query = "SELECT id, title FROM job ORDER BY title ASC";
$jobs = $pdo->query($query);

$query = "SELECT id, title FROM skill_category ORDER BY title ASC";
$skill_categories = $pdo->query($query)->fetchAll();

$query = "SELECT id, title FROM skill ORDER BY title ASC";
$skills = $pdo->query($query);

$experience_skills = [];
if (isset($experience)) {
    $query = "SELECT skill FROM experience_skill WHERE experience=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$experience["id"]]);
    $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $experience_skills = $result;
}
?>

<form method="POST">
  <input hidden name="id" value="<?= $experience["id"] ?>" />

  <fieldset>
    <legend>General infos</legend>

    <label>
      <span>Kind</span>
      <select name="kind">
          <?php foreach (EXPERIENCE_KIND as $kind => $label) { ?>
            <option value="<?= $kind ?>" <?= $experience["kind"] === $kind ? "selected" : "" ?>>
              <?= $label ?>
            </option>
          <?php } ?>
        </select>
    </label>

    <label>
      <span>Title</span>
      <input required spellcheck="true" name="title" value="<?= $experience["title"] ?>" />
    </label>

    <label>
      <span>Brief</span>
      <textarea name="brief" spellcheck="true" rows="2"><?= $experience["brief"] ?></textarea>
    </label>

    <label>
      <span>Details</span>
      <div id="editor-container">
        <div id="editor"><?= $experience["details"] ?></div>
      </div>
      <textarea name="details" id="details-input" style="display:none;"><?= $experience["details"] ?></textarea>
    </label>

  </fieldset>

  <fieldset>
    <legend>Dates</legend>

    <fieldset class="inline-field">
      <label>
        <span>From</span>
        <input required type="date" name="started" value="<?= $experience["started"] ?>" />
      </label>

      <label>
        <span>To</span>
        <input type="date" name="ended" value="<?= $experience["ended"] ?>"/>
      </label>
    </fieldset>
  </fieldset>

  <fieldset class="inline-field">
    <legend>Location</legend>
    <label>
      <span>Country</span>
      <input name="country" placeholder="FR" pattern="[A-Z]{0,2}" value="<?= trim($experience["country"]) ?>"/>
    </label>

    <label>
      <span>ZIP Code</span>
      <input name="zip" placeholder="75001" value="<?= $experience["zip"] ?>"/>
    </label>
  </fieldset>

  <fieldset class="inline-field add-or-select">
    <legend>Organization</legend>
    <label class="trigger">
      <label>New</label>
      <input type="radio" name="organization_mode" value="add" />
    </label>

    <fieldset class="inline-field">
      <label>
        <span>Title</span>
        <input name="organization_title" />
      </label>

      <label>
        <span>Link</span>
        <input type="url" name="organization_link" />
      </label>
    </fieldset>

    <label class="trigger">
      <label>Existing</label>
      <input type="radio" name="organization_mode" value="select" checked />
    </label>

    <fieldset>
      <label>Organization</label>
      <select name="organization_id" value="<?= $experience["organization_id"] ?>">
        <option value="">None</option>
        <?php foreach ($organizations as $organization) {
            ?>
        <option value="<?= $organization["id"] ?>"
          <?= $experience["organization_id"] === $organization["id"] ? "selected" : "" ?> >
          <?= $organization["title"] ?>
        </option>
        <?php
        }
?>
      </select>
    </fieldset>
  </fieldset>

  <fieldset class="inline-field add-or-select">
    <legend>Job</legend>
    <label class="trigger">
      <label>New</label>
      <input type="radio" name="job_mode" value="add" />
    </label>

    <fieldset class="inline-field">
      <label>
        <span>Title</span>
        <input name="job_title" />
      </label>

      <label>
        <span>Brief</span>
        <input name="job_brief" />
      </label>
    </fieldset>

    <label class="trigger">
      <label>Existing</label>
      <input type="radio" name="job_mode" value="select" checked />
    </label>

    <fieldset>
      <label>Job</label>
      <select name="job_id" value="<?= $experience["job_id"] ?>">
        <option value="">None</option>
        <?php foreach ($jobs as $job) {
            ?>
        <option value="<?= $job["id"] ?>"
          <?= $experience["job_id"] === $job["id"] ? "selected" : "" ?> >
          <?= $job["title"] ?>
        </option>
        <?php
        }
?>
      </select>
    </fieldset>
  </fieldset>

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
            <option value="<?= $level ?>" >
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
          <?= in_array($skill["id"], $experience_skills) ? "selected" : "" ?> >
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
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'indent': '-1'}, { 'indent': '+1' }],
        ['link'],
        ['clean']
      ]
    }
  });

  const form = document.querySelector('form');
  const details = document.getElementById('details-input');

  form.addEventListener('submit', function() {
    details.value = quill.getSemanticHTML().replaceAll("&nbsp;", " ");
  });
</script>
