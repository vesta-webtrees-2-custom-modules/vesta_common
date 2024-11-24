<?php

namespace Vesta\Hook\HookInterfaces;

use Cissee\WebtreesExt\Requests;
use Cissee\WebtreesExt\Services\ModuleServiceExt;
use Closure;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;
use Psr\Http\Message\ServerRequestInterface;

class IndividualFactsTabExtenderUtils {

    public static function updateOrder(
        ModuleInterface $moduleForPrefsOrder,
        ServerRequestInterface $request) {

        $order = Requests::getArray($request, 'order');
        //set als preference
        $pref = implode(',', $order);
        $moduleForPrefsOrder->setPreference('ORDER_FACTS_TAB', $pref);
    }

    public static function accessibleModules(
        ModuleInterface $module,
        Tree $tree,
        UserInterface $user): Collection {

        return self::sort(
                $module,
                ModuleServiceExt::findBySpecificComponent(
                        IndividualFactsTabExtenderInterface::class,
                        IndividualFactsTabExtenderUtils::moduleSpecificComponentName($module),
                        $tree,
                        $user));
    }

    //should only be used for control panel, otherwise always use accessibleModules()!
    public static function modules(
        ModuleInterface $moduleForPrefsOrder,
        $include_disabled = false): Collection {

        return self::sort(
                $moduleForPrefsOrder,
                \Vesta\VestaUtils::get(ModuleService::class)
                    ->findByInterface(IndividualFactsTabExtenderInterface::class, $include_disabled));
    }

    private static function sort(
        ModuleInterface $moduleForPrefsOrder,
        Collection $coll): Collection {

        $pref = $moduleForPrefsOrder->getPreference('ORDER_FACTS_TAB');
        if ($pref === null) {
            $pref = '';
        }
        $order = explode(',', $pref);
        $order = array_flip($order);

        return $coll
                ->map(function (IndividualFactsTabExtenderInterface $module) use ($order) {
                    if (array_key_exists($module->name(), $order)) {
                        $rank = $order[$module->name()];
                        $module->setFactsTabUIElementOrder($rank);
                    }
                    return $module;
                })
                ->sort(IndividualFactsTabExtenderUtils::sorter());
    }

    public static function sorter(): Closure {
        return function (IndividualFactsTabExtenderInterface $x, IndividualFactsTabExtenderInterface $y): int {
            return $x->getFactsTabUIElementOrder() <=> $y->getFactsTabUIElementOrder();
        };
    }

    //we want to be able to configure per-module, e.g.
    //hide research suggestions in place history, but not in individual facts and events tab
    //therefore do not just use same constant string for all modules!
    public static function moduleSpecificComponentName(
        ModuleInterface $module): string {

        return "VestaFactsVia" . $module->name();
    }

}
