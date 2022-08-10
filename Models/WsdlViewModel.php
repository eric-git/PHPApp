<?php

namespace Usi\Models;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Models\BaseViewModel.php");

class WsdlViewModel extends BaseViewModel
{
    public readonly string $OriginalWsdl;
    public string $Wsdl;

    function __construct(string $originalWsdl)
    {
        $this->OriginalWsdl = $originalWsdl;
    }
}
