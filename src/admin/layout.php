<?php
if (!isset($title)) {
    $title = "Page";
}

if (!isset($content)) {
    $content = "No content";
}
?>

<!DOCTYPE html>
<html lang="en-US">

<head>
  <title>
    <?= $title ?> - Portfolio Administration
  </title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible+Mono:ital,wght@0,200..800;1,200..800&family=Courier+Prime:ital,wght@0,400;0,700;1,400;1,700&family=Cutive+Mono&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="/admin.css">
  <link rel="stylesheet" href="https://cdn.linearicons.com/free/1.0.0/icon-font.min.css">
  <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
</head>

<body>
  <header>
    <h1><a href="/">Portfolio</a>/<?= $title ?></h1>

    <nav>
      <menu>
        <li>
          <a href="/admin/projects/">Projects</a>
        </li>
        <li>
          <a href="/admin/projects/categories/">Projects/Categories</a>
        </li>
        <li>
          <a href="/admin/experiences/">Experiences</a>
        </li>
        <li>
          <a href="/admin/skills/">Skills</a>
        </li/>
        <li>
          <a href="/admin/skills/categories/">Skills/Categories</a>
        </li>
        <li>
          <a href="/admin/organizations/">Organizations</a>
        </li>
        <li>
          <a href="/admin/jobs/">Jobs</a>
        </li>
      </menu>
    </nav>
  </header>

  <?= $content ?>
</body>
