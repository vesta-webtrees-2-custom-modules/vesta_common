<?php

namespace Cissee\WebtreesExt\Contracts;

use Fisharebest\Webtrees\Fact;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Vesta\Model\GenericViewElement;


interface AssociateFactUtils {
    
    public function gveLabelForAsso(
        ModuleInterface $module,
        string $label,
        Fact $fact,
        Individual $record): GenericViewElement;
}
