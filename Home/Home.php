<?php

declare(strict_types=1);

namespace Usi\Views;
?>

<!DOCTYPE HTML>
<html>

<head>
    <title>The future has landed</title>
    <?php require_once(sprintf("%s/Shared/Head.php", $_SERVER["DOCUMENT_ROOT"]));
    require_once(sprintf("%s/Shared/Styles.php", $_SERVER["DOCUMENT_ROOT"])); ?>
</head>

<body class="is-preload landing">
    <div id="page-wrapper">

        <!-- Header -->
        <?php require_once(sprintf("%s/Shared/Header.php", $_SERVER["DOCUMENT_ROOT"])); ?>

        <!-- Banner -->
        <section id="banner">
            <video muted="1" autoplay="1" preload="auto" style="position: absolute; width:100vw; max-width: 100vw; height:100vh; max-height: 100vh; right: 0; z-index: 1; opacity: .38;">
                <source src="https://dm0qx8t0i9gc9.cloudfront.net/watermarks/video/HZxZ2vJlxiusaqweu/airplane-landing-at-sunset-long-focus-lens-beautiful-very-realistic-animation_beel0cvbl__1ce0a53882d454b75e8964151718e117__P360.mp4" type="video/mp4">
            </video>
            <div class="content">
                <header>
                    <h2>The future has landed</h2>
                    <p>And there are no hoverboards or flying cars.<br />
                        Just apps. Lots of mother flipping apps.</p>
                </header>
                <span class="image">
                    <img src="assets/css/images/usi.png" alt="Banner" />
                </span>
            </div>
            <a href="#0" class="goto-next scrolly">Next</a>
        </section>

        <!-- Sections -->
        <?php
        $numberOfSections = count($sections);
        for ($counter = 0; $counter < $numberOfSections; $counter++) {
            $section = $sections[$counter];
        ?>
            <section id="<?= strval($counter) ?>" class="spotlight <?= $counter % 2 === 0 ? "right" : "left"; ?>">
                <div class="image fit main">
                    <img src="<?= $section->Background; ?>" alt="<?= htmlentities($section->Title); ?>" />
                </div>
                <div class="content">
                    <header>
                        <h2><?= htmlentities($section->Title); ?></h2>
                        <p><?= htmlentities($section->SubTitle); ?></p>
                    </header>
                    <p><?= htmlentities($section->Description); ?></p>
                    <ul class="actions">
                        <li><a class="button" href="/<?= $section->ActionViewName ?>"><?= htmlentities($section->ActionText); ?></a></li>
                    </ul>
                    <?php
                    if ($counter + 1 < $numberOfSections) { ?>
                        <a href="#<?= strval($counter + 1); ?>" class="goto-next scrolly">Next</a>
                    <?php } ?>
                </div>
            </section>
        <?php } ?>

        <!-- Footer -->
        <?php require_once(sprintf("%s/Shared/Footer.php", $_SERVER["DOCUMENT_ROOT"])); ?>
    </div>

    <!-- Scripts -->
    <?php require_once(sprintf("%s/Shared/Scripts.php", $_SERVER["DOCUMENT_ROOT"])); ?>
</body>

</html>