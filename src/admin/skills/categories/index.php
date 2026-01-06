<?php

$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn, $_ENV["DB_USER"], $_ENV["DB_PASSWORD"]);

$query = "SELECT id, title FROM skill_category ORDER BY title ASC";
$categories = $pdo->query($query);

$title = "Skills/Categories";
ob_start();
?>

<main>
  <section>
    <menu>
      <a href="add.php">Add</a>
    </menu>

    <table>
      <thead>
        <tr>
          <th>Title</th>
          <th>Actions</th>
        </tr>
      </thead>

      <tbody>
        <?php
  foreach ($categories as $category) {
      ?>
        <tr>
          <th scope="row">
            <a href="edit.php?id=<?= $category["id"] ?>">
              <?= $category["title"] ?>
            </a>
          </th>
          <td>
            <a href="remove.php?id=<?= $category["id"] ?>">
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
