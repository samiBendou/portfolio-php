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
    href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="/admin.css">
</head>

<body>
  <header>
    <a href="/">Portfolio</a>
    <nav>
      <menu>
        <li>
          <a href="/admin/projects/">Projects</a>
        </li>
        <li>
          <a href="/admin/experiences/">Experiences</a>
        </li>
        <li>
          <a href="/admin/skills/">Skills</a>
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
