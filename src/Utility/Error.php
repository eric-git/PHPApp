<?php

declare(strict_types=1);

namespace Usi\Views;

$errorCode = isset($_GET["code"]) && is_numeric($_GET["code"]) ? intval($_GET["code"]) : 404;
$title = $errorCode === 404 ? "Not Found" : "Error Occurred";
?>

<!DOCTYPE HTML>
<html>

<head>
  <title><?= $title ?></title>
  <?php require_once(sprintf("%s/Shared/Head.php", $_SERVER["DOCUMENT_ROOT"]));
  require_once(sprintf("%s/Shared/Styles.php", $_SERVER["DOCUMENT_ROOT"])); ?>
</head>

<body class="is-preload landing">
  <div id="page-wrapper">

    <!-- Header -->
    <?php require_once(sprintf("%s/Shared/Header.php", $_SERVER["DOCUMENT_ROOT"])); ?>

    <!-- Main -->
    <section class="wrapper special fade-up">
      <div class="container">
        <header class="major">
          <h2><?= $title ?></h2>
        </header>
        <div class="box alt">
          <div class="error-container">
            <i class="icon solid fa-4x fa-<?= $errorCode === 404 ? "ghost" : "bug" ?>"></i>
            <p><?= $errorCode === 404 ? "Oops, we cannot find the page you are looking for" : "Well, this is unexpected. Please try again later or contact us" ?>.</p>
          </div>
        </div>
      </div>
  </div>
  </section>

  <!-- Footer -->
  <?php require_once(sprintf("%s/Shared/Footer.php", $_SERVER["DOCUMENT_ROOT"])); ?>
  </div>

  <!-- Scripts -->
  <?php require_once(sprintf("%s/Shared/Scripts.php", $_SERVER["DOCUMENT_ROOT"])); ?>
</body>

</html>
