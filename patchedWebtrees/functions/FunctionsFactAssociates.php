<?php

namespace Cissee\WebtreesExt\Functions;

use Cissee\WebtreesExt\MoreI18N;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Fact;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\GedcomRecord;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\ModuleChartInterface;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Fisharebest\Webtrees\Module\RelationshipsChartModule;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Services\RelationshipService;
use Fisharebest\Webtrees\View;
use Vesta\Hook\HookInterfaces\IndividualFactsTabExtenderInterface;
use Vesta\Hook\HookInterfaces\IndividualFactsTabExtenderUtils;
use Vesta\Model\GenericViewElement;
use function app;
use function view;

class FunctionsFactAssociates {
  
    public static function getOutputForRelationship(
        ModuleInterface $module,
        Fact $event,
        Individual $person,
        Individual $associate,
        $relationship_name_prefix,
        $relationship_name,
        $relationship_name_suffix,
        $inverse): GenericViewElement {

        $outs = IndividualFactsTabExtenderUtils::accessibleModules($module, $person->tree(), Auth::user())
            ->map(function (IndividualFactsTabExtenderInterface $module) use ($event, $person, $associate, $relationship_name_prefix, $relationship_name, $relationship_name_suffix, $inverse) {
              return $module->hFactsTabGetOutputForAssoRel($event, $person, $associate, $relationship_name_prefix, $relationship_name, $relationship_name_suffix, $inverse);
            })
            ->toArray();

        foreach ($outs as $out) {
            if ($out == null) {
                //first return wins
                return null; //do not proceed
            }
            if (($out->getMain() !== '') || ($out->getScript() !== '')) {
                //first return wins
                return $out;
            }
        }

        //nothing hooked or only empty string(s) returned: fallback!
        return FunctionsFactAssociates::getOutputForRelationshipFallback(
                    $event,
                    $person,
                    $associate,
                    $relationship_name_prefix,
                    $relationship_name,
                    $relationship_name_suffix,
                    $inverse);
    }
  
    protected function getOutputForRelationshipFallback(
        Fact $event,
        Individual $person,
        Individual $associate,
        $relationship_name_prefix,
        $relationship_name,
        $relationship_name_suffix,
        $inverse): GenericViewElement {

        //TODO use $inverse here?

        $main = "";

        $module = app(ModuleService::class)->findByComponent(ModuleChartInterface::class, $person->tree(), Auth::user())->first(static function (ModuleInterface $module) {
            return $module instanceof RelationshipsChartModule;
        });

        if ($module instanceof RelationshipsChartModule) {
            $main = '<a href="' . $module->chartUrl($associate, ['xref2' => $person->xref()]) . '" rel="nofollow">' . $relationship_name_prefix . $relationship_name . $relationship_name_suffix . '</a>';
        }

        //$main = '<a href="' . e(route('relationships', ['xref1' => $associate->xref(), 'xref2' => $person->xref(), 'ged' => $person->tree()->name()])) . '" rel="nofollow">' . $relationship_name_prefix . $relationship_name . $relationship_name_suffix . '</a>';

        //use the relationship name even if no chart is configured
        //(note: webtrees doesn't do this in fact-association-structure view)
        $main = $relationship_name_prefix . $relationship_name . $relationship_name_suffix;
        return new GenericViewElement($main, '');
    }
    
    public static function getHtmlAndPushScript(
        ModuleInterface $module,
        Fact $fact,
        GedcomRecord $parent,
        Individual $person,
        /*mixed - php 8 only!*/ $associates,
        array $values): string {
        
        foreach ($associates as $associate) {
            $relationship_name = app(RelationshipService::class)->getCloseRelationshipName($associate, $person);
            if ($relationship_name === '') {
                //[RC] adjusted we use a different fallback
                //$relationship_name = I18N::translate('Relationship');
                $relationship_name = MoreI18N::xlate('No relationship found');
            }

            //[RC] adjusted
            $relationship_name_suffix = '';
            if ($parent instanceof Family) {
                // For family ASSO records (e.g. MARR), identify the spouse with a sex icon
                $sex = '<small>' . view('icons/sex', ['sex' => $associate->sex()]) . '</small>';
                $relationship_name_suffix = $sex;
            }

            //[RC] adjusted
            $out = FunctionsFactAssociates::getOutputForRelationship(
                $module, 
                $fact, 
                $person, 
                $associate, 
                ' — ', 
                $relationship_name, 
                $relationship_name_suffix, 
                false);
            
            if ($out != null) {
                $values[] = $out->getMain();
                $script = $out->getScript();
                if ($script !== '') {
                    View::push('javascript');
                    echo $script;
                    View::endpush();
                }
            }
        }
        
        //[RC] adjusted
        $value = implode('', $values);
        
        return $value;
    }
}
