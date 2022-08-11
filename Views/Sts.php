<?php

declare(strict_types=1);

// this violates MVC pattern, will be removed later...
namespace Usi\Views;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Controllers\StsController.php");

use Usi\Controllers\StsController;

$stsController = new StsController();
$stsViewModel = $stsController->populateViewModel();
?>

<!DOCTYPE HTML>
<html>

<head>
    <title>Test STS</title>
    <?php require_once($_SERVER['DOCUMENT_ROOT'] . "\Views\Shared\Head.php"); ?>
    <?php require_once($_SERVER['DOCUMENT_ROOT'] . "\Views\Shared\Styles.php"); ?>
    <script>
        var text = "<node g=\"hhh\">abc</node>";
    </script>

</head>

<body class="is-preload landing">
    <div id="page-wrapper">

        <!-- Header -->
        <?php require_once($_SERVER['DOCUMENT_ROOT'] . "\Views\Shared\Header.php"); ?>

        <!-- Main -->
        <section class="wrapper special fade-up">
            <div class="container">
                <header class="major">
                    <h2>Test STS</h2>
                </header>
                <div class="box alt" style="text-align: initial;">
                    <details class="row" open>
                        <summary class="col-12">
                            <h3 style="display: inline-block; margin-right: 3em;">Request</h3>
                            <a id="btnSubmit" href="#" class="button">Regenerate & Invoke</a>
                        </summary>
                        <pre><code id="txtRequest" class="language-xml"><?= \htmlentities($stsViewModel->RequestXml) ?></code></pre>
                    </details>
                    <details class="row" style="margin-top: 1em;" open>
                        <summary class="col-12">
                            <h3 style="display: inline-block;">Response</h3>
                        </summary>
                        <pre><code id="txtResponse" class="language-xml"><?= \htmlentities($stsViewModel->ResponseXml) ?></code></pre>
                    </details>
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
    <script src="/assets/js/sts.js"></script>
</body>

</html>