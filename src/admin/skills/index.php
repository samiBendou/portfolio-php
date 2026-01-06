<?php

require_once("consts.php");

$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn);

$query = "SELECT  skill.id, 
                  skill.title, 
                  skill.level, 
                  skill_category.title as category  
          FROM skill 
          LEFT JOIN skill_category ON skill.category = skill_category.id 
          ORDER BY skill.title ASC";
$skills = $pdo->query($query);

$title = "Skills";
ob_start();
?>

<main>
  <section>
    <table>
      <thead>
        <tr>
          <th>Title</th>
          <th>Level</th>
          <th>Category</th>
          <th>Actions</th>
        </tr>
      </thead>

      <tbody>
        <?php
  foreach ($skills as $skill) {
      ?>
        <tr>
          <th scope="row">
            <a href="edit.php?id=<?= $skill["id"] ?>">
              <?= $skill["title"] ?>
            </a>
          </th>
          <td>
            <?= SKILL_LEVEL[$skill["level"]] ?>
          </td>
          <td>
            <?= $skill["category"] ?>
          </td>
          <td>
            <a href="remove.php?id=<?= $skill["id"] ?>">
              Remove
            </a>
          </td>
        </tr>
        <?php
  }
?>
      </tbody>
    </table>

  </section>
</main>

<?php
$content = ob_get_clean();
include($_SERVER["DOCUMENT_ROOT"] . "/admin/layout.php");
