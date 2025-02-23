<?php

declare(strict_types=1);

namespace Usi\Views;
?>

<!DOCTYPE HTML>
<html>

<head>
    <title>Test Operations</title>
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
                    <h2>Test Operations</h2>
                </header>
                <div class="box alt">
                    <div class="row" style="flex-wrap: nowrap;">
                        <aside class="stack">
                            <?php foreach ($operationsViewModel->Operations as $operation) { ?>
                                <a href="#" class="button"><?= $operation->Name ?></a>
                            <?php } ?>
                        </aside>
                        <div class="row stack-container">
                            <details open>
                                <summary>
                                    <h3>Data</h3>
                                    <a id="btnReset" href="#" class="button">Reset</a>
                                    <a id="btnSubmit" href="#" class="button">Invoke</a>
                                </summary>
                                <form style="display: flex;">
                                    <input type="hidden" name="controller" value="OperationsController" />
                                    <input type="hidden" name="function" value="invoke" />
                                    <input type="hidden" name="param_0" value="" />
                                    <textarea id="txtData" name="param_1" rows="8" style="resize: none; flex-basis: 100%;"></textarea>
                                </form>
                            </details>
                            <details open>
                                <summary>
                                    <h3>Request</h3>
                                </summary>
                                <pre><code id="txtRequest" class="language-xml"></code></pre>
                            </details>
                            <details open>
                                <summary>
                                    <h3>Response</h3>
                                </summary>
                                <pre><code id="txtResponse" class="language-xml"></code></pre>
                            </details>
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
    <script src="/assets/js/operations.js"></script>
    <?php
    foreach ($operationsViewModel->Operations as $operation) { ?>
        <script id="<?= $operation->Name ?>" type="application/xml"><?= $operation->RequestTemplate ?></script>
    <?php } ?>
</body>

</html>