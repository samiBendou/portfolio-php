<?php

$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $query = "INSERT INTO project(title, brief, details, started, ended, link, category, experience)
              VALUES (:title, :brief, :details, :started, :ended, :link, :category, :experience)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
      ":title" => htmlspecialchars($_POST["title"]),
      ":brief" => htmlspecialchars($_POST["brief"]),
      ":details" => $_POST["details"],
      ":started" => $_POST["started"],
      ":ended" => $_POST["ended"] ? $_POST["ended"] : null,
      ":link" => $_POST["link"] ? $_POST["link"] : null,
      ":category" => $_POST["category_id"],
      ":experience" => $_POST["experience_id"] ? $_POST["experience_id"] : null
    ]);

    $id = $pdo->lastInsertId();
    $query = "INSERT INTO skill(title, category, level) VALUES (:title, :category, :level)";
    $stmt = $pdo->prepare($query);
    $new_skills = [];

    foreach (range(0, 4) as $idx) {
        if ($_POST["skill_title"][$idx]) {
            $stmt->execute([
              ":title" => $_POST["skill_title"][$idx],
              ":category" => $_POST["skill_category"][$idx],
              ":level" => $_POST["skill_level"][$idx]
            ]);
            array_push($new_skills, $pdo->lastInsertId());
        }
    }
    $existing_skills = $_POST["skill_id"] ? $_POST["skill_id"] : [];
    $skills = array_merge($existing_skills, $new_skills);
    if (!empty($skills)) {
        $placeholders = implode(',', array_fill(0, count($skills), '(?, ?)'));
        $query = "INSERT INTO project_skill(skill, project) VALUES $placeholders";
        $values = [];
        foreach ($skills as $skill) {
            array_push($values, $skill, $id);
        }
        $stmt = $pdo->prepare($query);
        $stmt->execute($values);
    }

    header("Location: index.php");
    exit;
}


$title = "Projects/add";
ob_start();
?>

<main>
  <?php include("form.php") ?>
</main>


<?php
$content = ob_get_clean();
include($_SERVER["DOCUMENT_ROOT"] . "/admin/layout.php");
