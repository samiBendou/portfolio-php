<?php

$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn, $_ENV["DB_USER"], $_ENV["DB_PASSWORD"]);

$query = "SELECT id, title, brief FROM job ORDER BY title ASC";
$jobs = $pdo->query($query);

$title = "Jobs";
ob_start();
?>

<main>
  <section>
    <table>
      <thead>
        <tr>
          <th>Title</th>
          <th>Brief</th>
          <th>Actions</th>
        </tr>
      </thead>

      <tbody>
        <?php
  foreach ($jobs as $job) {
      ?>
        <tr>
          <th scope="row">
            <a href="edit.php?id=<?= $job["id"] ?>">
              <?= $job["title"] ?>
            </a>
          </th>
          <td>
            <?= strip_tags($job["brief"]) ?>
          </td>
          <td>
            <a href="remove.php?id=<?= $job["id"] ?>">
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
