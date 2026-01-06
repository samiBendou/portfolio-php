<?php

if (!isset($category)) {
    $category = [];
}
?>

<form method="POST">
  <input hidden name="id" value="<?= $category["id"] ?>" />

  <fieldset>
    <legend>General infos</legend>
    <label>
      <span>Title</span>
      <input required spellcheck="true" name="title" value="<?= $category["title"] ?>" />
    </label>
  </fieldset>

  <button>Submit</button>
</form>
