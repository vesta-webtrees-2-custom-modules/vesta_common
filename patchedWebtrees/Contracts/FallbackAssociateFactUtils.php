<?php

namespace Cissee\WebtreesExt\Contracts;

use Cissee\WebtreesExt\MoreI18N;
use Fisharebest\Webtrees\Fact;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Vesta\Model\GenericViewElement;

//may be used when asso facts aren't actually expected (i.e. when dispaying facts elsewhere)
class FallbackAssociateFactUtils implements AssociateFactUtils {
    
    public function gveLabelForAsso(
        ModuleInterface $module,
        string $label,
        Fact $fact,
        Individual $record): GenericViewElement {
        
        $main = $label . ": " . MoreI18N::xlate('Associate');
        return GenericViewElement::create($main);
    }
}
