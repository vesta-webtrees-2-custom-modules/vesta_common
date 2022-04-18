<?php

namespace Cissee\WebtreesExt\Contracts;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\GedcomRecord;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Vesta\Hook\HookInterfaces\IndividualFactsTabExtenderInterface;
use Vesta\Hook\HookInterfaces\IndividualFactsTabExtenderUtils;
use Vesta\Model\GenericViewElement;
use Vesta\VestaUtils;
use function view;

//functions called from views displaying fact lists
class FactListUtils {

    public static function getOutputBeforeTab(
        ModuleInterface $module,
        GedcomRecord $record,
        bool $ajax) {
        
        $tree = $record->tree();
        $a1 = IndividualFactsTabExtenderUtils::accessibleModules($module, $tree, Auth::user())
                ->map(function (IndividualFactsTabExtenderInterface $m) use ($tree) {
                  return $m->hFactsTabRequiresModalVesta($tree);
                })
                ->toArray();
    
        $gve1 = GenericViewElement::createEmpty();
        if (!empty($a1)) {
            $script = implode($a1);
            $html = view(VestaUtils::vestaViewsNamespace() . '::modals/ajax-modal-vesta', [
                    'ajax' => $ajax, //tab is loaded via ajax, family-page isn't!
                    'select2Initializers' => [$script]
            ]);
    
            $gve1 = GenericViewElement::create($html);
        }        
    
        $a2 = IndividualFactsTabExtenderUtils::accessibleModules($module, $tree, Auth::user())
            ->map(function (IndividualFactsTabExtenderInterface $m) use ($record) {
              return $m->hFactsTabGetOutputBeforeTab($record);
            })
            ->toArray();

        return GenericViewElement::implode([$gve1, GenericViewElement::implode($a2)]);
    }

    public static function getOutputAfterTab(
        ModuleInterface $module,
        GedcomRecord $record,
        bool $ajax) {
        
        $a = IndividualFactsTabExtenderUtils::accessibleModules($module, $record->tree(), Auth::user())
            ->map(function (IndividualFactsTabExtenderInterface $m) use ($record, $ajax) {
              return $m->hFactsTabGetOutputAfterTab($record, $ajax);
            })
            ->toArray();

        return GenericViewElement::implode($a);
    }
  
    public static function getOutputInDescriptionBox(
        ModuleInterface $module,
        GedcomRecord $record) {
        
        return GenericViewElement::implode(IndividualFactsTabExtenderUtils::accessibleModules($module, $record->tree(), Auth::user())
                            ->map(function (IndividualFactsTabExtenderInterface $m) use ($record) {
                              return $m->hFactsTabGetOutputInDBox($record);
                            })
                            ->toArray());
    }

    public static function getOutputAfterDescriptionBox(
        ModuleInterface $module,
        Individual $person) {
        
        return GenericViewElement::implode(IndividualFactsTabExtenderUtils::accessibleModules($module, $person->tree(), Auth::user())
                            ->map(function (IndividualFactsTabExtenderInterface $m) use ($person) {
                              return $m->hFactsTabGetOutputAfterDBox($person);
                            })
                            ->toArray());
    }

}
