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
                <div class="box alt">
                    <div class="row stack-container">
                        <details open>
                            <summary>
                                <h3>Request</h3>
                                <a id="btnSubmit" href="#" class="button">Refresh</a>
                            </summary>
                            <pre><code id="txtRequest" class="language-xml"><?= \htmlentities($stsViewModel->RequestXml) ?></code></pre>
                        </details>
                        <details open>
                            <summary>
                                <h3>Response</h3>
                            </summary>
                            <pre><code id="txtResponse" class="language-xml"><?= \htmlentities($stsViewModel->ResponseXml) ?></code></pre>
                        </details>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <?php require_once($_SERVER['DOCUMENT_ROOT'] . "\Views\Shared\Footer.php"); ?>
    </div>

    <!-- Scripts -->
    <?php require_once($_SERVER['DOCUMENT_ROOT'] . "\Views\Shared\Scripts.php"); ?>
    <script src="/assets/js/sts.js"></script>
</body>

</html>