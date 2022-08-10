<?php

namespace Usi\Controllers;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Controllers\BaseController.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "\Models\HomeViewModel.php");

use Usi\Models\SectionCollection;
use Usi\Models\Section;

class HomeController extends BaseController
{
    public function populateViewModel(): SectionCollection
    {
        $sections = new SectionCollection(
            new Section(
                "WSDL",
                "WSDL is an XML notation for describing a web service. A WSDL definition tells a client how to compose a web service request and describes the interface that is provided by the web service provider.",
                "Following our versioning standards, for each version of the web service, XML namespaces are updated. Therefore, the service client needs to be updated to supply correct SOAP requests.",
                "https://images.pexels.com/photos/532173/pexels-photo-532173.jpeg",
                "View WSDL",
                "wsdl"
            ),
            new Section(
                "Federation Authentication & STS",
                "A Security Token Service (STS) is used to issue user a SAML 2.0 security token, which is requred by each SOAP request.",
                "Our security requirements are detailed in the WSDL, please refer to \"<wsp:Policy>\" element. Users are required to obtain a security token from our IDP - ATO. Then, this token will be attached to the SOAP header of each request and will be used to authenticate users on our service side.",
                "https://images.pexels.com/photos/272980/pexels-photo-272980.jpeg",
                "Test STS",
                "sts"
            ),
            new Section(
                "USI Service",
                "A WCF Contract is an agreement between the two parties, in other words a Service and a Client. Contracts can be categorized as behavioral, aka. Service/Operation/Fault Contracts or structural, aka. Data/Message Contract.",
                "Our Contracts are detailed in WSDL, please refer to \"<wsdl:types>\" element for type schema, \"<wsdl:message>\" elements for message contracts and \"<wsdl:portType>\" for service operations",
                "https://images.pexels.com/photos/12708081/pexels-photo-12708081.jpeg",
                "Test Operations",
                "operations"
            )
        );

        return $sections;
    }
}
