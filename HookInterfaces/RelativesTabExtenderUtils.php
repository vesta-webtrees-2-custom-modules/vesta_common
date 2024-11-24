<?php

namespace Vesta\Hook\HookInterfaces;

use Closure;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;
use Psr\Http\Message\ServerRequestInterface;
use Cissee\WebtreesExt\Requests;

class RelativesTabExtenderUtils {

  public static function updateOrder(ModuleInterface $moduleForPrefsOrder, ServerRequestInterface $request) {
    $order = Requests::getArray($request, 'order');
    //set als preference
    $pref = implode(',', $order);
    $moduleForPrefsOrder->setPreference('ORDER_FAMILIES_TAB', $pref);
  }

  public static function accessibleModules(ModuleInterface $moduleForPrefsOrder, Tree $tree, UserInterface $user): Collection {
    return self::sort($moduleForPrefsOrder, \Vesta\VestaUtils::get(ModuleService::class)
                            ->findByComponent(RelativesTabExtenderInterface::class, $tree, $user));
  }

  public static function modules(ModuleInterface $moduleForPrefsOrder, $include_disabled = false): Collection {
    return self::sort($moduleForPrefsOrder, \Vesta\VestaUtils::get(ModuleService::class)
                            ->findByInterface(RelativesTabExtenderInterface::class, $include_disabled));
  }

  private static function sort(ModuleInterface $moduleForPrefsOrder, Collection $coll): Collection {
    $pref = $moduleForPrefsOrder->getPreference('ORDER_FAMILIES_TAB');
    if ($pref === null) {
      $pref = '';
    }
    $order = explode(',', $pref);
    $order = array_flip($order);

    return $coll
                    ->map(function (RelativesTabExtenderInterface $module) use ($order) {
                      if (array_key_exists($module->name(), $order)) {
                        $rank = $order[$module->name()];
                        $module->setRelativesTabUIElementOrder($rank);
                      }
                      return $module;
                    })
                    ->sort(RelativesTabExtenderUtils::sorter());
  }

  public static function sorter(): Closure {
    return function (RelativesTabExtenderInterface $x, RelativesTabExtenderInterface $y): int {
      return $x->getRelativesTabUIElementOrder() <=> $y->getRelativesTabUIElementOrder();
    };
  }

}
