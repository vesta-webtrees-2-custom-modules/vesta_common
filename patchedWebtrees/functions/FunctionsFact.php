<?php

namespace Cissee\WebtreesExt\Functions;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Fact;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Vesta\Hook\HookInterfaces\IndividualFactsTabExtenderInterface;
use Vesta\Hook\HookInterfaces\IndividualFactsTabExtenderUtils;
use Vesta\Model\GenericViewElement;

class FunctionsFact {
    
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    //functions called from custom view 'fact'
  
    public static function additionalStyles(
        ModuleInterface $module,
        Fact $fact): array {
        
        $styles = [];

        $additions = IndividualFactsTabExtenderUtils::accessibleModules($module, $fact->record()->tree(), Auth::user())
                ->map(function (IndividualFactsTabExtenderInterface $m) {
                  return $m->hFactsTabGetStyleadds();
                })
                ->toArray();

        foreach ($additions as $a) {
            foreach ($a as $id => $cssClass) {
                if ($fact->id() === $id) {
                    $styles[] = trim($cssClass);
                }
            }
        }
        return $styles;
    }
  
    public static function gveAdditionalEditControls(
        ModuleInterface $module,
        Fact $fact): GenericViewElement {
        
        $additions = IndividualFactsTabExtenderUtils::accessibleModules($module, $fact->record()->tree(), Auth::user())
            ->map(function (IndividualFactsTabExtenderInterface $m) use ($fact) {
              return $m->hFactsTabGetAdditionalEditControls($fact);
            })
            ->toArray();
            
        return GenericViewElement::implode($additions);
    }
}
