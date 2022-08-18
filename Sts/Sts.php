<?php

declare(strict_types=1);

namespace Usi\Views;
?>

<!DOCTYPE HTML>
<html>

<head>
    <title>Test STS</title>
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
                    <h2>Test STS</h2>
                </header>
                <div class="box alt">
                    <div class="row stack-container">
                        <details open>
                            <summary>
                                <h3>Request</h3>
                                <a id="btnSubmit" href="#" class="button">Refresh</a>
                            </summary>
                            <pre><code id="txtRequest" class="language-xml"><?= htmlentities($stsViewModel->RequestXml) ?></code></pre>
                        </details>
                        <details open>
                            <summary>
                                <h3>Response</h3>
                            </summary>
                            <pre><code id="txtResponse" class="language-xml"><?= htmlentities($stsViewModel->ResponseXml) ?></code></pre>
                        </details>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <?php require_once(sprintf("%s/Shared/Footer.php", $_SERVER["DOCUMENT_ROOT"])); ?>
    </div>

    <!-- Scripts -->
    <?php require_once(sprintf("%s/Shared/Scripts.php", $_SERVER["DOCUMENT_ROOT"])); ?>
    <script src="/assets/js/sts.js"></script>
</body>

</html>