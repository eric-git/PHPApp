<?php

declare(strict_types=1);

// this violates MVC pattern, will be removed later...
namespace Usi\Views;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Controllers\WsdlController.php");

use Usi\Controllers\WsdlController;

$wsdlController = new WsdlController();
$wsdlViewModel = $wsdlController->populateViewModel();
?>

<!DOCTYPE HTML>
<html>

<head>
    <title>View WSDL</title>
    <?php require_once($_SERVER['DOCUMENT_ROOT'] . "\Views\Shared\Head.php"); ?>
    <?php require_once($_SERVER['DOCUMENT_ROOT'] . "\Views\Shared\Styles.php"); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.6.0/styles/vs2015.min.css" />
</head>

<body class="is-preload landing">
    <div id="page-wrapper">

        <!-- Header -->
        <?php require_once($_SERVER['DOCUMENT_ROOT'] . "\Views\Shared\Header.php"); ?>

        <!-- Main -->
        <section class="wrapper special fade-up">
            <div class="container">
                <header class="major">
                    <h2>WSDL</h2>
                </header>
                <div class="box alt" style="text-align: initial;">
                    <div class="row">
                        <pre style="height: 60vh;"><code class="language-xml"><?= htmlentities($wsdlViewModel->Wsdl); ?></code></pre>
                    </div>
                </div>
                <footer class="major"></footer>
            </div>
        </section>

        <!-- Footer -->
        <?php require_once($_SERVER['DOCUMENT_ROOT'] . "\Views\Shared\Footer.php"); ?>
    </div>

    <!-- Scripts -->
    <?php require_once($_SERVER['DOCUMENT_ROOT'] . "\Views\Shared\Scripts.php"); ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.6.0/highlight.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.6.0/languages/xml.min.js"></script>
    <script src="/assets/js/wsdl.js"></script>
</body>

</html>