<?php
if (!isset($experience)) {
    $experience = [];
}

$kind = $experience["kind"];


$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn);

$query = "SELECT id, title FROM organization";
$organizations = $pdo->query($query);

$query = "SELECT id, title FROM job";
$jobs = $pdo->query($query);
?>

<form method="POST">
  <input hidden name="id" value="<?= $experience["id"] ?>" />

  <fieldset>
    <legend>General infos</legend>

    <label>
      <span>Kind</span>
      <select name="kind">
        <option value="internship" <?=$kind === "internship" ? "selected" : "" ?> >Internship</option>
        <option value="job" <?=$kind === "job" ? "selected" : "" ?> >Job</option>
        <option value="education" <?=$kind === "education" ? "selected" : "" ?> >Education</option>
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
      <textarea name="details" spellcheck="true" rows="16"><?= $experience["details"] ?></textarea>
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
      <input  type="radio" name="organization_mode" value="add" />
    </label>
    
    <fieldset class="inline-field">
      <label>
        <span>Title</span>
        <input name="organization_title"/> 
      </label> 

      <label>
        <span>Link</span>
        <input type="url" name="organization_link"/> 
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
      <option value="<?= $organization["id"] ?>" <?= $experience["organization_id"] === $organization["id"] ? "selected" : "" ?> >
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
      <label>Brief</label>
      <input  type="radio" name="job_mode" value="add" />
    </label>
    
    <fieldset class="inline-field">
      <label>
        <span>Title</span>
        <input name="job_title"/> 
      </label> 

      <label>
        <span>Brief</span>
        <input name="job_brief"/> 
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
      <option value="<?= $job["id"] ?>" <?= $experience["job_id"] === $job["id"] ? "selected" : "" ?> >
        <?= $job["title"] ?>
      </option>
<?php
}
?>
      </select>
    </fieldset>
  </fieldset>
  </fieldset>

  <button>Submit</button>
</form>

