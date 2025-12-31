
<?php

$db_path = $_ENV["DB_URL"];
$pdo = new PDO($db_path);

$id = $_SERVER['REQUEST_METHOD'] == 'GET' ? $_GET["id"] : $_POST["id"];

$query = "SELECT  experience.id as id,
                  experience.title AS title, 
                  job.title AS job, 
                  kind,
                  brief,
                  details,
                  location,
                  start, 
                  end, 
                  organization.title AS organization 
          FROM experience 
          JOIN experience_job ON experience_job.experience = experience.id
          JOIN job ON experience_job.job = job.id
          JOIN experience_organization ON experience_organization.experience = experience.id
          JOIN organization ON experience_organization.organization = organization.id
          WHERE id=?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);

$experience = $stmt->fetch();
if (!$experience) {
    http_response_code(404);
    exit;
}

$title = 'Edit experience';
ob_start();

?>

<main>
  <h1><? = $title ?></h1>
  <form>
    <section>
      <h2>General infos</h2>
      
      <input hidden name="id" value="<? $experience["id"] ?>"/>

      <label>
          Kind
          <select name="kind" value="<? $experience["kind"] ?>">
            <option value="internship">Intership</option>            
            <option value="job">Job</option>            
            <option value="education">Education</option>            
          </select>
      </label>

    </section>  
     

  </form>
</main>


<?php
$content = ob_get_clean();
include($_SERVER["DOCUMENT_ROOT"] . "/admin/layout.php");
