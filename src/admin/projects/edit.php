<?php
$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn);

$id = $_SERVER['REQUEST_METHOD'] == 'GET' ? $_GET["id"] : $_POST["id"];

$query = "SELECT  project.id as id,
                  project.title AS title,
                  project.brief AS brief,
                  project.details AS details,
                  project.started AS started,
                  project.ended AS ended,
                  project.link AS link,
                  project.category AS category_id,
                  project.experience AS experience_id,
                  project_category.title AS category,
                  experience.title AS experience
          FROM project
          LEFT JOIN project_category ON project.category=project_category.id
          LEFT JOIN experience ON project.experience=experience.id
          WHERE project.id=?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);

$project = $stmt->fetch();
if (!$project) {
    http_response_code(404);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $query = "DELETE FROM project_skill WHERE project=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);

    $query = "INSERT INTO skill(title, kind, level) VALUES (:title, :kind, :level)";
    $stmt = $pdo->prepare($query);
    $new_skills = [];

    foreach (range(0, 4) as $idx) {
        if ($_POST["skill_title"][$idx]) {
            $stmt->execute([
              ":title" => $_POST["skill_title"][$idx],
              ":kind" => $_POST["skill_kind"][$idx],
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

    $query = "UPDATE project
              SET   title=:title,
                    brief=:brief,
                    details=:details,
                    started=:started,
                    ended=:ended,
                    link=:link,
                    category=:category,
                    experience=:experience
              WHERE id=:id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
      ":id" => $id,
      ":title" => htmlspecialchars($_POST["title"]),
      ":brief" => htmlspecialchars($_POST["brief"]),
      ":details" => $_POST["details"],
      ":started" => $_POST["started"],
      ":ended" => $_POST["ended"] ? $_POST["ended"] : null,
      ":link" => $_POST["link"] ? $_POST["link"] : null,
      ":category" => $_POST["category_id"],
      ":experience" => $_POST["experience_id"] ? $_POST["experience_id"] : null
    ]);

    header("Location: index.php");
    exit;
}


$title = "Projects/$id";
ob_start();
?>

<main>
  <?php include("form.php") ?>
</main>

<?php
$content = ob_get_clean();
include($_SERVER["DOCUMENT_ROOT"] . "/admin/layout.php");
