<?php

namespace Usi;

use Usi\Infrastructure\Configuration;
use Usi\Infrastructure\ConfigurationCollection;
use Usi\Infrastructure\ProxySettings;
use Usi\Infrastructure\StsSettings;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Infrastructure\Configuration.php");

const CurrentEnvironment = "3PT";

class Config
{
    private static ?ConfigurationCollection $configurations;

    public static function getConfiguration(): Configuration
    {
        if (!isset(self::$configurations)) {
            self::populateConfigurations();
        }

        foreach (self::$configurations as $configuration) {
            if (strcasecmp($configuration->Environment, CurrentEnvironment) === 0) {
                return $configuration;
            }
        }
    }

    private static function populateConfigurations()
    {
        self::$configurations = new ConfigurationCollection(
            // LOCAL
            new Configuration(
                "LOCAL",
                new StsSettings(
                    "https://softwareauthorisations.acc.ato.gov.au/R3.0/S007v1.3/service.svc",
                    "https://3pt.portal.usi.gov.au/service/usiservice.svc"
                ),
                "https://localhost:4443/service/v5/usiservice.svc",
                "VA1803",
                new ProxySettings(
                    "dmz",
                    8080
                )
            ),

            // DEV
            new Configuration(
                "DEV",
                new StsSettings(
                    "https://softwareauthorisations.acc.ato.gov.au/R3.0/S007v1.3/service.svc",
                    "https://3pt.portal.usi.gov.au/service/usiservice.svc"
                ),
                "https://dev.portal.usi.gov.au/service/v5/usiservice.svc",
                "VA1803"
            ),

            // 3PT
            new Configuration(
                "3PT",
                new StsSettings(
                    "https://softwareauthorisations.acc.ato.gov.au/R3.0/S007v1.3/service.svc",
                    "https://3pt.portal.usi.gov.au/service/usiservice.svc"
                ),
                "https://3pt.portal.usi.gov.au/service/v5/usiservice.svc",
                "VA1803"
            ),

            // PROD
            new Configuration(
                "PROD",
                new StsSettings(
                    "https://softwareauthorisations.ato.gov.au/R3.0/S007v1.3/service.svc",
                    "https://portal.usi.gov.au/service/usiservice.svc"
                ),
                "https://portal.usi.gov.au/service/v5/usiservice.svc",
                "VA1803"
            ),
        );
    }
}
