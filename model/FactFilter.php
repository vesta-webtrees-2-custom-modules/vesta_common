<?php


namespace Vesta\Model;

use Fisharebest\Webtrees\Fact;

interface FactFilter {
    
    public function filter(Fact $fact): bool;
}
