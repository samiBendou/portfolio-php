<?php

$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn);

$query = "SELECT  project.id as id,
                  project.title AS title, 
                  project_category.title AS category,
                  project.started AS started, 
                  project.ended AS ended, 
                  experience.title AS experience 
          FROM project 
          LEFT JOIN project_category ON project.category=project_category.id
          LEFT JOIN experience ON project.experience=experience.id
          ORDER BY id DESC";
$projects = $pdo->query($query);

$title = "Projects";
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
          <th>Category</th>
          <th>Start</th>
          <th>End</th>
          <th>Experience</th>
          <th>Actions</th>
        </tr>
      </thead>

      <tbody>
        <?php
  foreach ($projects as $project) {
      ?>
        <tr>
          <th scope="row">
            <a href="edit.php?id=<?= $project["id"] ?>">
              <?= $project["title"] ?>
            </a>
          </th>
          <td>
            <?= $project["category"] ?>
          </td>
          <td>
            <?= $project["started"] ?>
          </td>
          <td>
            <?= $project["ended"] ? $project["ended"] : "<em>Present</em>" ?>
          </td>
          <td>
            <?= $project["experience"] ?>
          </td>
          <td>
            <a href="remove.php?id=<?= $project["id"] ?>">
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
