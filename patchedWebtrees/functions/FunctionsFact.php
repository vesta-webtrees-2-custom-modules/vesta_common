<?php

namespace Cissee\WebtreesExt\Functions;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Fact;
use Fisharebest\Webtrees\GedcomRecord;
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
        GedcomRecord $record,
        Fact $fact): array {
        
        $styles = [];

        //skip for 'histo' facts (improves performance if there are many such facts)
        if ($fact->id() === 'histo') {
            return [];
        }
        
        $additions = IndividualFactsTabExtenderUtils::accessibleModules($module, $fact->record()->tree(), Auth::user())
                ->map(function (IndividualFactsTabExtenderInterface $m) use ($record, $fact) {
                  return $m->hFactsTabGetStyleadds($record, $fact);
                })
                ->toArray();

        foreach ($additions as $a) {
            foreach ($a as $cssClass) {
                $styles[] = trim($cssClass);
            }
        }
        return $styles;
    }
  
    public static function gveAdditionalEditControls(
        ModuleInterface $module,
        Fact $fact): GenericViewElement {
        
        //skip for 'histo' facts (improves performance if there are many such facts)
        if ($fact->id() === 'histo') {
            return GenericViewElement::createEmpty();
        }
        
        $additions = IndividualFactsTabExtenderUtils::accessibleModules($module, $fact->record()->tree(), Auth::user())
            ->map(function (IndividualFactsTabExtenderInterface $m) use ($fact) {
              return $m->hFactsTabGetAdditionalEditControls($fact);
            })
            ->toArray();
            
        return GenericViewElement::implode($additions);
    }
}
