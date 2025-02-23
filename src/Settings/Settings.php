<?php

declare(strict_types=1);

namespace Usi\Views;
?>

<!DOCTYPE HTML>
<html>

<head>
    <title>Settings</title>
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
                    <h2>Settings</h2>
                </header>
                <div class="box alt">
                    <div class="row" style="flex-wrap: nowrap;">
                        <aside class="stack">
                            <a href="#" class="button primary" data-section="settings">Settings</a>
                            <a href="#" class="button" data-section="info">PHP Info</a>
                        </aside>
                        <div id="settings" class="stack">
                            <dl>
                                <dt class="box">
                                    Current Environment: <em id="txtCurrentEnvironment"><?= $settingsViewModel->CurrentEnvironment ?></em>;
                                    Current Org Code: <em id="txtCurrentOrgCode"><?= $settingsViewModel->CurrentOrgCode ?></em>
                                </dt>
                                <dd>
                                    <a id="btnReset" href="#" class="button">Reset</a>
                                    <a id="btnSubmit" href="#" class="button">Apply</a>
                                </dd>
                            </dl>
                            <form>
                                <input type="hidden" name="controller" value="SettingsController"></input>
                                <input type="hidden" name="function" value="update"></input>
                                <dl>
                                    <dt class="row">
                                        <h3 class="col-3">Environment</h3>
                                        <select id="cbEnvironment" class="col-3" name="param_0">
                                            <?php foreach ($settingsViewModel->ConfigurationCollection as $configuration) {
                                                $selected = strcasecmp($settingsViewModel->CurrentEnvironment, $configuration->Environment) === 0; ?>
                                                <option value="<?= $configuration->Environment ?>" <?= $selected ? "selected" : "" ?> data-current="<?= $selected ? "true" : "false" ?>">
                                                    <?= $configuration->Environment ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <span class="col-6"></span>
                                    </dt>
                                    <dd>
                                        <?php foreach ($settingsViewModel->ConfigurationCollection as $configuration) { ?>
                                            <dl id="env-<?= $configuration->Environment ?>" class="row" style="display: none;">
                                                <dt class="col-3">USI Service URL</dt>
                                                <dd class="col-9"><?= htmlentities($configuration->UsiServiceUrl) ?></dd>
                                                <dt class="col-3">Default Organisation Code</dt>
                                                <dd class="col-9"><?= htmlentities($configuration->DefaultOrgCode) ?></dd>
                                                <dt class="col-3">STS Service URL</dt>
                                                <dd class="col-9"><?= htmlentities($configuration->Sts->IssuerUrl) ?></dd>
                                                <dt class="col-3">Applies To</dt>
                                                <dd class="col-9"><?= htmlentities($configuration->Sts->AppliesTo) ?></dd>
                                            </dl>
                                        <?php } ?>
                                    </dd>
                                    <?php foreach ($settingsViewModel->ConfigurationCollection as $configuration) { ?>
                                        <dt id="org-<?= $configuration->Environment ?>" class="row" style="display: none;">
                                            <h3 class="col-3">Organisation</h4>
                                                <select class="col-9" name="param_1" data-default="<?= $configuration->DefaultOrgCode ?>">
                                                    <?php foreach ($configuration->KeyStore->Credentials as $orgKeyData) {
                                                        $isCurrent = strcasecmp($configuration->Environment, $settingsViewModel->CurrentEnvironment) === 0 &&
                                                            strcasecmp($orgKeyData->Code, $settingsViewModel->CurrentOrgCode) === 0;
                                                        $selected = $isCurrent || strcasecmp($orgKeyData->Code, $configuration->DefaultOrgCode) === 0; ?>
                                                        <option value="<?= $orgKeyData->Code ?>" <?= $selected ? "selected" : "" ?> data-current="<?= $isCurrent ? "true" : "false" ?>">
                                                            <?= $orgKeyData->LegalName ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                        </dt>
                                        <dd>
                                            <?php foreach ($configuration->KeyStore->Credentials as $orgKeyData) { ?>
                                                <dl class="row" id="key-<?= $configuration->Environment ?>-<?= $orgKeyData->Code ?>" style="display: none;">
                                                    <dt class="col-3">ID</dt>
                                                    <dd class="col-9"><?= htmlentities($orgKeyData->Id) ?></dd>
                                                    <dt class="col-3">Code</dt>
                                                    <dd class="col-9"><?= htmlentities($orgKeyData->Code) ?></dd>
                                                    <dt class="col-3">Name1</dt>
                                                    <dd class="col-9"><?= htmlentities($orgKeyData->Name1) ?></dd>
                                                    <dt class="col-3">Name2</dt>
                                                    <dd class="col-9"><?= htmlentities($orgKeyData->Name2) ?></dd>
                                                    <dt class="col-3">Legal Name</dt>
                                                    <dd class="col-9"><?= htmlentities($orgKeyData->LegalName) ?></dd>
                                                    <dt class="col-3">Person ID</dt>
                                                    <dd class="col-9"><?= htmlentities($orgKeyData->PersonId) ?></dd>
                                                    <dt class="col-3">Serial Number</dt>
                                                    <dd class="col-9"><?= htmlentities($orgKeyData->SerialNumber) ?></dd>
                                                    <dt class="col-3">Creation Date</dt>
                                                    <dd class="col-9"><?= htmlentities(date_format($orgKeyData->CreationDate, "c")) ?></dd>
                                                    <dt class="col-3">Not Before</dt>
                                                    <dd class="col-9"><?= htmlentities(date_format($orgKeyData->NotBefore, "c")) ?></dd>
                                                    <dt class="col-3">Not After</dt>
                                                    <dd class="col-9"><?= htmlentities(date_format($orgKeyData->NotAfter, "c")) ?></dd>
                                                    <dt class="col-3">Private Key Password</dt>
                                                    <dd class="col-9"><?= htmlentities($orgKeyData->PrivateKeyPassword) ?></dd>
                                                    <dt class="col-3">Integrity Value</dt>
                                                    <dd class="col-9"><?= htmlentities($orgKeyData->IntegrityValue) ?></dd>
                                                    <dt class="col-3">Credential Salt</dt>
                                                    <dd class="col-9"><?= htmlentities($orgKeyData->CredentialSalt) ?></dd>
                                                    <dt class="col-3">Credential Type</dt>
                                                    <dd class="col-9"><?= htmlentities($orgKeyData->CredentialType) ?></dd>
                                                    <dt class="col-3">SHA1 Fingerprint</dt>
                                                    <dd class="col-9"><?= htmlentities($orgKeyData->Sha1fingerprint) ?></dd>
                                                </dl>
                                            <?php } ?>
                                        </dd>
                                    <?php } ?>
                                </dl>
                            </form>
                        </div>
                        <div id="info" class="row" style="margin: initial; flex: auto; display: none;">
                            <p>
                                <i class="icon solid fa-circle-info"></i>
                                <em>Please manually open hyperlinks in the document in a new tab as they may not work on the page.</em>
                            </p>
                            <iframe class="box col-12" src="/Utility/PHPInfo.php" style="height: 60vh; padding: initial;"></iframe>
                        </div>
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
    <script src="/assets/js/settings.js"></script>
</body>

</html>