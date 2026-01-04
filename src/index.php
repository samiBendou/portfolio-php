<?php

$cache_file = __DIR__ . "/cache.php";

if (file_exists($cache_file)) {
    $cache = include $cache_file;
    extract($cache);
}

$new_cache = [];
$now = new DateTime();
if (!isset($metar) || (isset($metar_ttl) && $metar_ttl->diff($now)->h >= 1)) {
    $ch = curl_init("https://api.checkwx.com/metar/LFPG");
    $headers = ["X-API-Key: 7e5f03e5409944749a9a5f059887d736"];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $metar = json_decode($response, true)["data"][0];
    $metar_ttl = $now;
}

$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn);

$subquery_exp =  "WITH  ranges AS ( SELECT job, started, COALESCE(ended, date('now')) AS ended FROM experience WHERE job IS NOT NULL),
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
            JSON_AGG(DISTINCT skill.title ORDER BY skill.title ASC) AS skills
          FROM job
          JOIN ($subquery_exp) AS dates ON job.id=dates.job
          LEFT JOIN experience ON experience.job = job.id
          LEFT JOIN experience_skill ON experience_skill.experience = experience.id
          LEFT JOIN skill ON experience_skill.skill = skill.id
          WHERE skill.title IS NOT NULL 
          GROUP BY job.id, job.title, job.brief, dates.started, dates.duration
          ORDER BY duration ASC";
$jobs = $pdo->query($query);

$query = "SELECT id, zip, country FROM location";
$locations = $pdo->query($query);

$ch = curl_init();
$key = "542893341774625209706x124087";

if (!isset($resolved_locations)) {
    $resolved_locations = [];
}

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
          <div><?= $metar ?> // HI! MY NAME IS SAMI DAHOUX // I MAKE SOFTWARE WITH MAGIC AND PASSION //</div>
        </div>
        <div>
          <div id="radar" role="figure">
          </div>

          <div>
            <h2 class="oldschool-heading">About me</h2>

            <dl>
              <dt>
                <?= $last_job["title"] ?>
              </dt> 
              <div>@</div>
              <dd>
                <a href="<?= $last_job["link"] ?>">
                  <?= $last_job["organization"] ?>
                </a>
              </dd>
              <dt>
                <?= $last_education["title"] ?>
              </dt>
              <div>@</div>
              <dd>
                <a href="<?= $last_education["link"] ?>">
                  <?= $last_education["organization"] ?>
                </a>
              </dd>
            </dl>

            <p>
              I'm an <em>8-years</em> experienced <em>software engineer</em> with a strong interest in
              <em>web technologies</em>
              and <em>cyber-physical systems</em>. I believe that engineering is an
              <em>artistic</em>
              and
              <em>creative</em> way to <em>imagine</em> and <em>build</em> the world we want to live in.
            </p>

            <p><strong>Make mankind dreams come true</strong></p>

            <ul class="cta-list">
              <li><a href="#contact" class="cta">Let's meet</a></li>
              <li><a href="/asses/DAHOUX-Sami-generic-resume.pdf" class="cta">Get Resume</a></li>
            </ul>

          </div>
        </div>
      </section>

      <section id="jobs">
        <h2>What I do</h2>
        <div>
          <?php foreach ($jobs as $job) {?>
          <label>
            <?= $job["title"] ?>
            <input type="radio" hidden checked name="selected" value="<?= $job["id"] ?>" />
          </label>

          <article>
              <div>
                <div>
                  <?= $job["title"] ?>
                </div>
              </div>
              <div>
                <p>
                  <?= $job["brief"] ?>
                </p>

                <h3>
                  <?= $job["title"] ?>
                </h3>
                <div class="marquee"> 
                <ul class="skills">
                    <?php foreach (json_decode($job["skills"], true) as $skill) { ?>
                    <li><?= $skill ?></li>
                    <?php } ?>
                </ul>
                </div>
                <a class="cta" href="/DAHOUX-Sami-generic-resume.pdf" target="_blank">Get resume</a>
                <dl>
                  <dd><?= $job["duration"] ?> year(s) of experience</dd>
                  <dd>Since <?= new DateTime($job["started"])->format("Y") ?></dd>
                </dl>
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
      <a href="https://www.clarapeker.com/">Clara Peker</a> 2017-2025, All Rights Reserved
    </p>
  </footer>
</body>
