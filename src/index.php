<?php

header('Access-Control-Allow-Origin: https://aviationweather.gov');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$dsn = $_ENV["DB_DSN"];
$pdo = new PDO($dsn);

$query = "SELECT id, title, brief FROM job";
$jobs = $pdo->query($query);
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
          <div>METAR LFPG 031030Z AUTO 28009KT 9999 FEW013 02/M00 Q100 // HI! MY NAME IS SAMI DAHOUX // I MAKE
            SOFTWARE WITH MAGIC AND PASSION //</div>
        </div>
        <div>
          <div id="radar" role="figure">
          </div>

          <div>
            <h2 class="oldschool-heading">About me</h2>

            <dl>
              <dd>
                <data class="icn location" value="75018-FR">
                  <a href="https://www.google.com/maps/place/75018+FR">
                    Paris
                  </a>
                </data>
              </dd>
              <dd>
                <data class="icn job" value="last-company">
                  <a href="https://last.job">
                    Company
                  </a>
                </data>
              </dd>
              <dd>
                <data class="icn education" value="last-school">
                  <a href="https://last.school">
                    Graduation
                  </a>
                </data>
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
          <?php
  foreach ($jobs as $job) {
      $query = "SELECT id, started, ended FROM experience WHERE job=?";
      $stmt = $pdo->prepare($query);
      $stmt->execute([$job["id"]]);
      $experiences = $stmt->fetchAll();
      $experiences_started = min(array_map(function ($e) { return $e["started"]; }, $experiences));
      $experiences_end = max(array_map(function ($e) { return $e["ended"]; }, $experiences));
      $started = new DateTime($experiences_started);
      $ended = new DateTime($experiences_end);
      $duration = $ended->diff($started);
      if ($duration->m >= 6) {
          $duration->y += 1;
      }


      $placeholders = implode(',', array_fill(0, count($experiences), '?'));
      $query = "SELECT DISTINCT title FROM skill JOIN experience_skill ON experience_skill.skill = skill.id WHERE experience_skill.experience IN ($placeholders)";
      $experiences_ids = array_map(function ($e) {return $e["id"]; }, $experiences);
      $stmt = $pdo->prepare($query);
      $stmt->execute($experiences_ids);
      $job_skills = $stmt->fetchAll();
      ?>
          <label>
            <?= $job["title"] ?>
            <input type="radio" checked name="selected" style="display: none;" value="<?= $job["id"] ?>" />
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
                <ul>
<?php foreach ($job_skills as $skill) {
    ?>
                    <li> // <?= $skill["title"] ?> </li>
<?php
}
      ?>
                </ul>
                </div>
                <dl>
                <dd><data><?= $duration->format("%y years") ?> of experience</data></dd>
                  <dd></dd>
                </dl>
                
              </div>
          </article>
          <?php
  }
?>
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
