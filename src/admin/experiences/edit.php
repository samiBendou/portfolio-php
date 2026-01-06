<?php
$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn, $_ENV["DB_USER"], $_ENV["DB_PASSWORD"]);

$id = $_SERVER['REQUEST_METHOD'] == 'GET' ? $_GET["id"] : $_POST["id"];

$query = "SELECT  experience.id as id,
                  experience.title AS title,
                  job.title AS job,
                  job.id AS job_id,
                  kind,
                  experience.brief AS brief,
                  details,
                  started,
                  ended,
                  organization.title AS organization,
                  organization.id AS organization_id,
                  location.country AS country,
                  location.zip AS zip,
                  location.id AS location_id 
          FROM experience
          LEFT JOIN job ON experience.job=job.id
          LEFT JOIN organization ON experience.organization=organization.id
          LEFT JOIN location ON experience.location=location.id
          WHERE experience.id=?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);

$experience = $stmt->fetch();
if (!$experience) {
    http_response_code(404);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $query = "INSERT INTO location(country, zip) 
              VALUES (:country, :zip) 
              ON CONFLICT (country, zip) DO UPDATE SET country=:country
              RETURNING id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
      ":country" => $_POST["country"],
      ":zip" => $_POST["zip"],
    ]);
    $location_id = $stmt->fetchColumn();

    $organization_id = $_POST["organization_id"];
    if ($_POST["organization_mode"] === "add") {
        $query = "INSERT INTO organization(title, link)
                  VALUES (:title, :link)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
          ":title" => $_POST["organization_title"],
          ":link" => $_POST["organization_link"] ? $_POST["organization_link"] : null,
        ]);
        $organization_id = $pdo->lastInsertId();
    }

    $job_id = $_POST["job_id"];
    if ($_POST["job_mode"] === "add") {
        $query = "INSERT INTO job(title, brief)
                  VALUES (:title, :brief)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
          ":title" => $_POST["job_title"],
          ":brief" => $_POST["job_brief"] ? $_POST["job_brief"] : null,
        ]);
        $job_id = $pdo->lastInsertId();
    }

    $query = "DELETE FROM experience_skill WHERE experience=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);

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
        $query = "INSERT INTO experience_skill(skill, experience) VALUES $placeholders";
        $values = [];
        foreach ($skills as $skill) {
            array_push($values, $skill, $id);
        }
        $stmt = $pdo->prepare($query);
        $stmt->execute($values);
    }

    $query = "UPDATE experience 
              SET   kind=:kind, 
                    title=:title, 
                    brief=:brief, 
                    details=:details, 
                    started=:started, 
                    ended=:ended,
                    location=:location,
                    organization=:organization,
                    job=:job
              WHERE id=:id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
      ":id" => $id,
      ":kind" => $_POST["kind"],
      ":title" => htmlspecialchars($_POST["title"]),
      ":brief" => htmlspecialchars($_POST["brief"]),
      ":details" => $_POST["details"],
      ":started" => $_POST["started"],
      ":ended" => $_POST["ended"] ? $_POST["ended"] : null,
      ":location" => $location_id,
      ":organization" => $organization_id ? $organization_id : null,
      ":job" => $job_id ? $job_id : null
    ]);

    header("Location: index.php");
    exit;
}


$title = "Experience/$id";
ob_start();
?>

<main>
  <?php include("form.php") ?>
</main>

<?php
$content = ob_get_clean();
include($_SERVER["DOCUMENT_ROOT"] . "/admin/layout.php");
