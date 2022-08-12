<?php

declare(strict_types=1);

// this violates MVC pattern, will be removed later...
namespace Usi\Views;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Controllers\OperationsController.php");

use Usi\Controllers\OperationsController;

$operationsController = new OperationsController();
$operationsViewModel = $operationsController->populateViewModel();
?>

<!DOCTYPE HTML>
<html>

<head>
    <title>Test Operations</title>
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
                    <h2>Test Operations</h2>
                </header>
                <div class="box alt row" style="margin:0; text-align: initial; column-gap: .3em; flex-wrap:nowrap;">
                    <aside class="operation-list">
                        <?php foreach ($operationsViewModel->Operations as $operation) { ?>
                            <a href="#" class="button"><?= $operation->Name ?></a>
                        <?php } ?>
                    </aside>
                    <div class="row operation-container">
                        <details open>
                            <summary class="col-12">
                                <h3 style="display: inline-block; margin-right: 3em;">Request</h3>
                                <a id="btnReset" href="#" class="button">Reset</a>
                                <a id="btnSubmit" href="#" class="button">Invoke</a>
                            </summary>
                            <form>
                                <div class="col-12" style="display:flex;">
                                    <input type="hidden" name="controller" value="OperationsController" />
                                    <input type="hidden" name="function" value="invoke" />
                                    <input type="hidden" name="param_0" value="" />
                                    <textarea id="txtRequest" name="param_1" rows="8" style="resize: none; flex-basis: 100%;"></textarea>
                                </div>
                            </form>
                        </details>
                        <details style="margin-top: 1em;" open>
                            <summary class="col-12">
                                <h3 style="display: inline-block;">Response</h3>
                            </summary>
                            <pre style="margin:0;"><code id="txtResponse" class="language-xml">Test</code></pre>
                        </details>
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
    <script src="/assets/js/operations.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.6.0/highlight.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.6.0/languages/xml.min.js"></script>
    <?php
    foreach ($operationsViewModel->Operations as $operation) { ?>
        <script id="<?= $operation->Name ?>" type="application/xml"><?= $operation->RequestTemplate ?></script>
    <?php } ?>
</body>

</html>