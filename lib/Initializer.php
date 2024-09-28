<?php

namespace PHPNomad\Guzzle\FetchIntegration;

use PHPNomad\Di\Interfaces\CanSetContainer;
use PHPNomad\Di\Traits\HasSettableContainer;
use PHPNomad\Loader\Interfaces\HasClassDefinitions;
use PHPNomad\Rest\Interfaces\FetchStrategy;

class Initializer implements HasClassDefinitions, CanSetContainer
{
    use HasSettableContainer;

    public function getClassDefinitions(): array
    {
        return [
            Strategies\FetchStrategy::class => FetchStrategy::class
        ];
    }
}