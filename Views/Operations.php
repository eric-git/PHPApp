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
                <div class="box alt">
                    <div class="row">
                        <ul style="text-align: initial;">
                            <?php
                            foreach ($operationsViewModel->Operations as $operation) {
                                echo "<li>" . $operation->Name . " -> <em>" . $operation->Signature . "</em></li>";
                            }
                            ?>
                        </ul>
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
</body>

</html>