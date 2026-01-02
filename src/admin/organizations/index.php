<?php

$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn);

$query = "SELECT id, title, link FROM organization ORDER BY title ASC";
$organizations = $pdo->query($query);

$title = "Organizations";
ob_start();
?>

<main>
  <section>
    <table>
      <thead>
        <tr>
          <th>Title</th>
          <th>Link</th>
          <th>Actions</th>
        </tr>
      </thead>

      <tbody>
        <?php
  foreach ($organizations as $organization) {
      ?>
        <tr>
          <th scope="row">
            <a href="edit.php?id=<?= $organization["id"] ?>">
              <?= $organization["title"] ?>
            </a>
          </th>
          <td>
            <?php
                if ($organization["link"]) {
                    ?>
              <a href="<?= $organization["link"] ?>">
                   <?= $organization["link"] ?>
              </a>
              <?php
                }
      ?>
          </td>
          <td>
            <a href="remove.php?id=<?= $organization["id"] ?>">
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
