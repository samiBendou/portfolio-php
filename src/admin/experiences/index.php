<?php

include("consts.php");

$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn, $_ENV["DB_USER"], $_ENV["DB_PASSWORD"]);

$query = "SELECT  experience.id as id,
                  experience.title AS title, 
                  job.title AS job, 
                  kind,
                  started, 
                  ended, 
                  organization.title AS organization 
          FROM experience 
          LEFT JOIN job ON experience.job=job.id
          LEFT JOIN organization ON experience.organization=organization.id
          ORDER BY id DESC";
$experiences = $pdo->query($query);

$title = "Experiences";
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
          <th>Kind</th>
          <th>Start</th>
          <th>End</th>
          <th>Job</th>
          <th>Organization</th>
          <th>Actions</th>
        </tr>
      </thead>

      <tbody>
        <?php
  foreach ($experiences as $experience) {
      ?>
        <tr>
          <th scope="row">
            <a href="edit.php?id=<?= $experience["id"] ?>">
              <?= $experience["title"] ?>
            </a>
          </th>
          <td>
            <?= EXPERIENCE_KIND[$experience["kind"]] ?>
          </td>
          <td>
            <?= $experience["started"] ?>
          </td>
          <td>
            <?= $experience["ended"] ? $experience["ended"] : "<em>Present</em>" ?>
          </td>
          <td>
            <?= $experience["job"] ?>
          </td>
          <td>
            <?= $experience["organization"] ?>
          </td>
          <td>
            <a href="remove.php?id=<?= $experience["id"] ?>">
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
