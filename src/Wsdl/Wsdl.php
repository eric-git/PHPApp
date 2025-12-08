<?php

declare(strict_types=1);

namespace Usi\Views;
?>

<!DOCTYPE HTML>
<html>

<head>
  <title>View WSDL</title>
  <?php require_once(sprintf("%s/Shared/Head.php", $_SERVER["DOCUMENT_ROOT"]));
  require_once(sprintf("%s/Shared/Styles.php", $_SERVER["DOCUMENT_ROOT"])); ?>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.6.0/styles/vs2015.min.css" />
</head>

<body class="is-preload landing">
  <div id="page-wrapper">

    <!-- Header -->
    <?php require_once(sprintf("%s/Shared/Header.php", $_SERVER["DOCUMENT_ROOT"])); ?>

    <!-- Main -->
    <section class="wrapper special fade-up">
      <div class="container">
        <header class="major">
          <h2>WSDL</h2>
        </header>
        <div class="box alt">
          <div class="row stack-container" style="row-gap: .3em;">
            <div>
              <a id="btnRefresh" href="#" class="button">Refresh</a>
            </div>
            <div>
              <pre style="height: 60vh;"><code id="txtWsdl" class="language-xml"><?= htmlentities($wsdlViewModel->Wsdl) ?></code></pre>
            </div>
          </div>
    </section>

    <!-- Footer -->
    <?php require_once(sprintf("%s/Shared/Footer.php", $_SERVER["DOCUMENT_ROOT"])); ?>
  </div>

  <!-- Scripts -->
  <?php require_once(sprintf("%s/Shared/Scripts.php", $_SERVER["DOCUMENT_ROOT"])); ?>
  <script src="/assets/js/wsdl.js"></script>
</body>

</html>
