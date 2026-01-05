<?php
require_once(__DIR__ . "/admin/skills/consts.php");

$cache_file = __DIR__ . "/cache.php";

if (file_exists($cache_file)) {
    $cache = include $cache_file;
    extract($cache);
}

$new_cache = [];
$now = new DateTime();
if (!isset($metar) || (isset($metar_ttl) && $metar_ttl->diff($now)->h >= 1)) {
    $ch = curl_init("https://api.checkwx.com/metar/LFPG");
    $key = $_ENV["CHECKWX_API_KEY"];
    $headers = ["X-API-Key: $key"];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $metar = json_decode($response, true)["data"][0];
    $metar_ttl = $now;
}

$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn);

$subquery =  "WITH  ranges AS ( SELECT job, started, COALESCE(ended, date('now')) AS ended FROM experience WHERE job IS NOT NULL),
                        groups AS ( SELECT job, started, ended, MAX(ended) OVER (ORDER BY started ROWS BETWEEN UNBOUNDED PRECEDING AND 1 PRECEDING) AS prev_max_end 
                                    FROM ranges ORDER BY started),
                        merged AS ( SELECT  job,
                                            started, 
                                            ended,
                                            CASE
                                              WHEN prev_max_end IS NULL OR started > prev_max_end THEN 1
                                                ELSE 0
                                              END AS is_new_group
                                    FROM groups
                                  ),
                        numbered AS ( SELECT job, started, ended, SUM(is_new_group) OVER (ORDER BY started) AS grp FROM merged), 
                        dates AS (    SELECT job, MAX(ended) as ended, MIN(started) as started FROM numbered GROUP BY grp, job)
                  SELECT job, MIN(started) AS started, ROUND(SUM(ended - started) / 365.0) AS duration FROM dates GROUP BY job";

$query = "SELECT
            job.id,
            job.title,
            job.brief,
            dates.started,
            dates.duration,
            JSON_AGG(DISTINCT skill.title ORDER BY skill.title ASC) FILTER (WHERE skill.title IS NOT NULL) AS skills
          FROM job
          JOIN ($subquery) AS dates ON job.id=dates.job
          LEFT JOIN experience ON experience.job = job.id
          LEFT JOIN experience_skill ON experience_skill.experience = experience.id
          LEFT JOIN skill ON experience_skill.skill = skill.id
          WHERE skill.title IS NOT NULL 
          GROUP BY job.id, dates.started, dates.duration
          ORDER BY duration ASC";
$jobs = $pdo->query($query);

$query = "SELECT id, zip, country FROM location";
$locations = $pdo->query($query);

$query = "SELECT  experience.id, 
                  experience.title, 
                  experience.kind, 
                  experience.brief,
                  experience.details,
                  experience.started, 
                  experience.ended, 
                  ((COALESCE(ended, date('now')) - started) / 30) AS duration, 
                  experience.location AS location,
                  organization.title as organization_title,
                  organization.link as organization_link,
                  JSON_AGG(DISTINCT skill.title ORDER BY skill.title ASC) FILTER (WHERE skill.title IS NOT NULL) AS skills
          FROM experience
          LEFT JOIN organization ON experience.organization = organization.id
          LEFT JOIN experience_skill ON experience_skill.experience = experience.id
          LEFT JOIN skill ON experience_skill.skill = skill.id
          GROUP BY  experience.id, organization.id
          ORDER BY experience.started DESC";
$experiences = $pdo->query($query);

$union_sub = "(SELECT skill, started, ended FROM experience_skill JOIN experience ON experience_skill.experience = experience.id) 
              UNION (SELECT skill, started, ended FROM project_skill JOIN project ON project_skill.project = project.id)";


$subquery =  "WITH  ranges AS ( SELECT skill, started, COALESCE(ended, date('now')) AS ended 
                                FROM ($union_sub)),
                        groups AS ( SELECT skill, started, ended, MAX(ended) OVER (ORDER BY started ROWS BETWEEN UNBOUNDED PRECEDING AND 1 PRECEDING) AS prev_max_end 
                                    FROM ranges ORDER BY started),
                        merged AS ( SELECT  skill,
                                            started, 
                                            ended,
                                            CASE
                                              WHEN prev_max_end IS NULL OR started > prev_max_end THEN 1
                                                ELSE 0
                                              END AS is_new_group
                                    FROM groups
                                  ),
                        numbered AS ( SELECT skill, started, ended, SUM(is_new_group) OVER (ORDER BY started) AS grp FROM merged), 
                        dates AS (    SELECT skill, MAX(ended) as ended, MIN(started) as started FROM numbered GROUP BY grp, skill)
                  SELECT skill, MIN(started) AS started, ROUND(SUM(ended - started) / 365.0) AS duration FROM dates GROUP BY skill";

$subquery2 = "SELECT experience_skill.skill as skill, JSON_AGG(experience.title) as titles
              FROM experience_skill JOIN experience ON experience_skill.experience = experience.id
              GROUP BY experience_skill.skill";


$query = "SELECT  skill.kind as kind,
                  JSON_AGG(JSON_BUILD_OBJECT( 'id', skill.id, 
                                              'title', skill.title, 
                                              'level', skill.level, 
                                              'duration', dates.duration, 
                                              'experiences', experiences.titles) 
                  ORDER BY skill.title) as skills 
          FROM skill  
          LEFT JOIN ($subquery) AS dates ON skill.id=dates.skill
          LEFT JOIN ($subquery2) AS experiences ON experiences.skill=skill.id
          GROUP BY skill.kind
          ORDER BY kind ASC";
$skills = $pdo->query($query);


$query = "SELECT  project.id, 
                  project.title, 
                  project_category.title AS category, 
                  project.brief,
                  project.details,
                  project.started, 
                  project.ended, 
                  ((COALESCE(project.ended, date('now')) - project.started) / 30) AS duration, 
                  experience.title as experience_title,
                  project.link as link,
                  JSON_AGG(DISTINCT skill.title ORDER BY skill.title ASC) FILTER (WHERE skill.title IS NOT NULL) AS skills
          FROM project
          LEFT JOIN experience ON project.experience = experience.id
          LEFT JOIN project_category ON project_category.id = project.category
          LEFT JOIN project_skill ON project_skill.project = project.id
          LEFT JOIN skill ON project_skill.skill = skill.id
          GROUP BY  project.id, project_category.title, experience.id
          ORDER BY project.category, project.started DESC";
$projects = $pdo->query($query)->fetchAll();

$query = "SELECT experience.title AS title, organization.title AS organization, organization.link AS link, location 
          FROM experience JOIN organization ON experience.organization = organization.id
          WHERE experience.kind='job' 
          ORDER BY started DESC LIMIT 1";
$stmt = $pdo->prepare($query);
$stmt->execute();
$last_job = $stmt->fetch();

$query = "SELECT experience.title AS title, organization.title AS organization, organization.link AS link, location
          FROM experience JOIN organization ON experience.organization = organization.id
          WHERE experience.kind='education' 
          ORDER BY started DESC LIMIT 1";
$stmt = $pdo->prepare($query);
$stmt->execute();
$last_education = $stmt->fetch();

$ch = curl_init();

if (!isset($resolved_locations)) {
    $resolved_locations = [];
}

$key = $_ENV["GEOCODE_API_KEY"];
foreach ($locations as $location) {
    if (!key_exists($location["id"], $resolved_locations)) {
        $zip = $location["zip"];
        $country = $location["country"];
        curl_setopt($ch, CURLOPT_URL, "https://geocode.xyz/$zip?region=$country&json=1&auth=$key");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = json_decode(curl_exec($ch), true);
        if (!isset($json["error"])) {
            $resolved = ["country" => $json["country"], "city" => $json["city"]];
            $resolved["state"] = "";
            if (is_string($json["statename"])) {
                $resolved["state"] = $json["statename"];
            } elseif (isset($json["state"])) {
                $resolved["state"] = $json["state"];
            } elseif (isset($json["region"])) {
                $resolved["state"] = $json["region"];
            }

            $resolved_locations[$location["id"]] = $resolved;
        }
    }
}


file_put_contents($cache_file, '<?php return ' . var_export([
    "resolved_locations" => $resolved_locations,
    "metar" => $metar,
    "metar_ttl" => $metar_ttl
  ], true) . ';');
?>

<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="utf-8" />
  <title>
    Sami Dahoux - Portfolio
  </title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible+Mono:ital,wght@0,200..800;1,200..800&family=Courier+Prime:ital,wght@0,400;0,700;1,400;1,700&family=Cutive+Mono&display=swap"
    rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="/effects.js"></script>
  <link rel="stylesheet" href="/style.css">
  <link rel="stylesheet" href="/modern-normalize.css">
  <link rel="stylesheet" href="/assets/fonts/lucide.css">
</head>

<body>
  <div>
    <header>
      <label>
        <input hidden type="checkbox" />
      </label>
      <a href="/">sami.bendou.space</a>
      <div>
        <h1 style="display: none;">Sami Dahoux's portfolio</h1>

        <nav>
          <ul>
            <li><a href="#about">About</a></li>
            <li><a href="#experiences">Experience</a></li>
            <li><a href="#projects">Projects</a></li>
            <li><a href="#contact">Contact</a></li>
          </ul>
        </nav>
    </header>

    <main id="main">
      <section id="about">

        <div id="metar" class="marquee" role="figure">
          <div>
            <?= $metar ?> // HI! MY NAME IS SAMI DAHOUX // I MAKE SOFTWARE WITH MAGIC AND PASSION //
          </div>
        </div>
        <div>
          <div id="radar" role="figure">
          </div>

          <div>
            <h2 class="oldschool-heading">About me</h2>

            <dl>
              <dt>
                Sami Dahoux
              </dt>
              <dt>
                <?= $last_job["title"] ?>
              </dt>
              <dd>
                <a href="<?= $last_job["link"] ?>">
                  <?= $last_job["organization"] ?>
                </a>
              </dd>
              <dt>
                <?= $last_education["title"] ?>
              </dt>
              <dd>
                <a href="<?= $last_education["link"] ?>">
                  <?= $last_education["organization"] ?>
                </a>
              </dd>
            </dl>

            <p>
              I am an engineer with a passionated with both
              <em>software</em>
              <em>cyber-physical systems</em>. I believe that engineering is an
              <em>artistic</em>
              and
              <em>creative</em> way to <em>imagine</em> and <em>build</em> the world we want to live in.
            </p>

            <ul class="cta-list">
              <li><a href="#contact" class="cta">Let's meet</a></li>
              <li><a href="/asses/DAHOUX-Sami-generic-resume.pdf" class="cta">Get Resume</a></li>
            </ul>

          </div>
        </div>
      </section>

      <section id="jobs">
        <h2>What I do</h2>
        <div class="right-screen">
          <?php foreach ($jobs as $job) {?>
          <label>
            <?= $job["title"] ?><input type="radio" checked name="jobs" value="<?= $job["id"] ?>" />
          </label>

          <article class="box">
              <dl>
                <dd>
                  <?= $job["duration"] ?> year(s) of experience
                </dd>
                <dd>Since
                  <?= new DateTime($job["started"])->format("Y") ?>
                </dd>
              </dl>

              <h3>
                <?= $job["title"] ?>
              </h3>
              <div class="marquee">
                <ul class="skills">
                  <?php foreach (json_decode($job["skills"], true) as $skill) { ?>
                  <li>
                    <?= $skill ?>
                  </li>
                  <?php } ?>
                </ul>
              </div>
              <a class="cta" href="/DAHOUX-Sami-generic-resume.pdf" target="_blank">Get resume</a>
              
              <div class="prose">
                <?= $job["brief"] ?>
              </div>
          </article>
          <?php } ?>
        </div>
      </section>

      <section id="skills">
        <h2>My know-how</h2>
        <div>
        <?php foreach ($skills as $kind) { ?>
        <div>
        <h3><?= $kind["kind"] ?></h3>
        <div class="list"> 
          <?php foreach (json_decode($kind["skills"], true) as $skill) { ?>
          <label class="level-<?= $skill["level"] ?>">
            <?= $skill["title"] ?><input type="radio" name="skill" value="<?= $skill["id"] ?>" />
        </label>
        <article class="box">
          <dl>
              <dd><?= $skill["duration"] ?> year(s)</dd>
              <dd><?= SKILL_LEVEL[$skill["level"]] ?></dd>
          </dl>
          <h4>Related</h4>
          <ul>
            <?php foreach ($skill["experiences"] as $experience) { ?>
            <li><?= $experience ?></li>
            <?php } ?>
          </ul>
        </article>
        <?php } ?>
        </div>
</div>
        <?php } ?>
        </div>
      </section>

      <section id="experiences">
        <h2>My experiences</h2>
        <?php foreach ($experiences as $experience) {
            $location = $resolved_locations[$experience["location"]];
            $url_location = urlencode($location["city"]) . "+" .  urlencode($location["country"]);
            ?>

        <article>
          <dl class="dates">
            <dd>
              <?= new DateTime($experience["started"])->format("M Y") ?>
            </dd>
            <dd>
              <?= $experience["ended"] ? new DateTime($experience["ended"])->format("M Y") : "Present" ?>
            </dd>
          </dl>
          <h3>
            <?= $experience["title"] ?>
          </h3>
          <?php if ($experience["organization_title"]) { ?>
          <a href="<?= $experience["organization_link"] ?>">
              <?= $experience["organization_title"] ?>
          </a>
          <?php } ?>
          <label>
            View more<input type="checkbox" value="<?= $experience["id"] ?>" />
          </label>

          <section class="box">
            <dl>
              <dd>
                <?= $experience["duration"] ?> month(s)
              </dd>
              <?php if ($location && $location["city"] !== null) { ?>
              <dd>
              <a href="<?= "https://www.google.com/maps/place/$url_location" ?>">
                  <?= $location["city"] ?>
                </a>
              </dd>
              <dd>
                <?= $location["state"] ?>
              </dd>
              <dd>
                <?= $location["country"] ?>
              </dd>
              <?php } ?>
          </dl>
            <div class="marquee">
              <ul class="skills">
                <?php foreach (json_decode($experience["skills"], true) as $skill) { ?>
                <li>
                  <?= $skill ?>
                </li>
                <?php } ?>
              </ul>
            </div>

            <a class="cta" href="/DAHOUX-Sami-generic-resume.pdf" target="_blank">Get resume</a>

            <p>
              <?= $experience["brief"] ?>
            </p>
            <div class="prose">
              <?= $experience["details"] ?>
            </div>
                      </section>
        </article>
        <?php } ?>
        </section>

      <section id="projects">
        <h2>My projects</h2>
        <div class="right-screen">
          <?php
            $last_category = $projects[0]["category"];
?>
            <h3><?= $last_category ?></h3>
            <?php foreach ($projects as $project) { ?>
            <?php if ($project["category"] !== $last_category) { ?>
            <h3><?= $project["category"]?></h3>
            <?php
  $last_category = $project["category"];
            }
                ?>
            <label>
            <?= $project["title"] ?><input type="radio" checked name="projects" value="<?= $project["id"] ?>" />
          </label>

          <article class="box">
              <dl>
                <dd>
                  <?= $project["duration"] ?> months
                </dd>
<dd>
              <?= new DateTime($project["started"])->format("M Y") ?>
            </dd>
            <dd>
              <?= $project["ended"] ? new DateTime($project["ended"])->format("M Y") : "Present" ?>
            </dd>
              </dl>

              <h4>
                <?= $project["title"] ?>
              </h4>
              <div class="marquee">
                <ul class="skills">
                  <?php foreach (json_decode($project["skills"], true) as $skill) { ?>
                  <li>
                    <?= $skill ?>
                  </li>
                  <?php } ?>
                </ul>
              </div>
              <a class="cta" href="/DAHOUX-Sami-generic-resume.pdf" target="_blank">Get resume</a>
              
              <p>  
                <?= $project["brief"] ?>
              </p>
              <div class="prose">
                <?= $project["details"] ?>
              </div>
          </article>
          <?php } ?>
        </div>
      </section>
    </main>
    <div id="alt-indicator">
      <div id="alt-center"></div>
      <div id="alt-pos"></div>
    </div>
  </div>
  <footer>
    <p id="copyright">
      © <a href="https://portfolio.bendou.space">Sami Dahoux</a> and
      <a href="https://www.clarapeker.com/">Clara Peker</a> 2026, All Rights Reserved
    </p>
  </footer>
</body>
