<?php

$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn);

$query = "SELECT id, title, kind, level FROM skill ORDER BY title ASC";
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
          <th>Kind</th>
          <th>Level</th>
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
            <?= $skill["kind"] ?>
          </td>
          <td>
            <?= $skill["level"] ?>
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
