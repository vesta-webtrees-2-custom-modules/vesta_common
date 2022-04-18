<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Module;

use Cissee\WebtreesExt\Contracts\FunctionsVestals;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ModuleVestalInterface extends ModuleInterface {
    
    public function useVestals(): bool;
    
    public function hideCoordinates(): bool;
    
    public function vestalsActionUrl(): string;
  
    public function postVestalsAction(
        ServerRequestInterface $request): ResponseInterface;
    
    public function functionsVestals(): FunctionsVestals;
        
}
