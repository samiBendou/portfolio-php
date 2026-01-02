<?php
if (!isset($experience)) {
    $experience = [];
}

$kind = $experience["kind"];
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
      <input name="country" placeholder="FR" pattern="[A-Z]{2}" value="<?= $experience["country"] ?>"/>
    </label>
    
    <label>
      <span>ZIP Code</span>
      <input name="zip" placeholder="75001" value="<?= $experience["zip"] ?>"/>
    </label>
  </fieldset>

  <button>Submit</button>
</form>

