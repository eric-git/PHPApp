<?php

declare(strict_types=1);

namespace Usi\Models;

require_once(sprintf("%s/Infrastructure/BaseViewModel.php", $_SERVER["DOCUMENT_ROOT"]));

class WsdlViewModel extends BaseViewModel
{
    public readonly string $OriginalWsdl;
    public string $Wsdl;

    public function __construct(string $originalWsdl)
    {
        $this->OriginalWsdl = $originalWsdl;
    }
}
