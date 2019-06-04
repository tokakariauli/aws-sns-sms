<?php

namespace ContractZero\SMSVerification;

use Illuminate\Support\Facades\Facade;

class ContractZeroSNSClientFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ContractZeroSNSClientSingleton';
    }
}
