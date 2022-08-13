<?php

declare(strict_types=1);

// this violates MVC pattern, will be removed later...
namespace Usi\Views;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Controllers\SettingsController.php");

use Usi\Controllers\SettingsController;

$settingsController = new SettingsController();
$settingsViewModel = $settingsController->populateViewModel();
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
                    <h2>Settings</h2>
                </header>
                <div class="box alt">
                    <div class="row" style="flex-wrap: nowrap;">
                        <aside class="stack">
                            <a href="#" class="button">Settings</a>
                            <a href="#" class="button">Info</a>
                        </aside>
                        <div class="row stack-container">
                            <details open>
                                <summary>
                                    <h3>System Settings</h3>
                                    <a id="btnReset" href="#" class="button">Reset</a>
                                    <a id="btnSubmit" href="#" class="button">Apply</a>
                                </summary>
                                <form class="stack-container">
                                    <dl>
                                        <dt>
                                            <label class="col-3" for="cbEnvironment">Environment</label>
                                            <select id="cbEnvironment" name="param_0">
                                                <option>text123</option>
                                                <option>text123</option>
                                                <option>text123</option>
                                                <option>text123</option>
                                            </select>
                                        </dt>
                                        <dd>

                                        </dd>
                                    </dl>
                                    <dl>
                                        <dt>
                                            <label class="col-3" for="cbOrg">Organisation</label>
                                            <select id="cbOrg" name="param_1">
                                                <option>text123</option>
                                                <option>text123</option>
                                                <option>text123</option>
                                                <option>text123</option>
                                            </select>
                                        </dt>
                                        <dd>

                                        </dd>
                                    </dl>
                                </form>
                            </details>
                            <details open>
                                <summary>
                                    <h3>System Info</h3>
                                </summary>
                                <ul>
                                    <li>test 1</li>
                                    <li>test 1</li>
                                    <li>test 1</li>
                                    <li>test 1</li>
                                    <li>test 1</li>
                                    <li>test 1</li>
                                    <li>test 1</li>
                                    <li>test 1</li>
                                </ul>
                            </details>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <?php require_once($_SERVER['DOCUMENT_ROOT'] . "\Views\Shared\Footer.php"); ?>
    </div>

    <!-- Scripts -->
    <?php require_once($_SERVER['DOCUMENT_ROOT'] . "\Views\Shared\Scripts.php"); ?>
    <script src="/assets/js/operations.js"></script>
    <?php
    foreach ($operationsViewModel->Operations as $operation) { ?>
        <script id="<?= $operation->Name ?>" type="application/xml"><?= $operation->RequestTemplate ?></script>
    <?php } ?>
</body>

</html>