<?php

$db_path = $_ENV["DB_URL"];
$pdo = new PDO($db_path);

$query = "SELECT  experience.id as id,
                  experience.title AS title, 
                  job.title AS job, 
                  started, 
                  ended, 
                  organization.title AS organization 
          FROM experience 
          LEFT JOIN job ON experience.job = job.id
          LEFT JOIN organization ON experience.organization = organization.id
          ORDER BY id DESC";
$experiences = $pdo->query($query);

$title = "Experiences";
ob_start();
?>

<main>
  <section>
    <h1><? = $title ?></h1>

    <menu>
      <a href="add.php">Add</a>
    </menu>

    <table>
      <thead>
        <tr>
          <th>Title</th>
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
