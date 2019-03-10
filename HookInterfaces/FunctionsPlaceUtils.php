<?php

namespace Vesta\Hook\HookInterfaces;

use Closure;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Request;
use Vesta\Model\PlaceStructure;
use function app;


class FunctionsPlaceUtils {
	
	//horizontally (hooks), then, if $fallbackViaParents, verticlly (via parent hierarchy)
	public static function getFirstLatLon($module, $fact, $gedcomPlace, $fallbackViaParents = true) {
		if (empty($gedcomPlace)) {
			return null;
		}
		
		$ps = PlaceStructure::create($gedcomPlace, $fact->record()->tree(), $fact->getTag(), $fact->attribute("DATE"));
		$functionsPlaceProviders = FunctionsPlaceUtils::accessibleModules($module, $fact->record()->tree(), Auth::user())
			/*
			//don't call all here - we only require first non-null non-empty! 		
			->map(function (FunctionsPlaceInterface $module) use ($ps) { 
				return $module->hPlacesGetLatLon($ps); 							
			})
			*/
			->toArray();

		foreach ($functionsPlaceProviders as $functionsPlaceProvider) {
			$ll = $functionsPlaceProvider->hPlacesGetLatLon($ps);
			if (($ll !== null) && (count($ll) >= 2)) {					
				//first one wins!
				return $ll;
			}
		}
		
		if (!$fallbackViaParents || (strpos($gedcomPlace, ',') === false)) {
			return null;
		}
		
		$parents = explode(',', $gedcomPlace);		
		$parents = array_reverse($parents);
		array_pop($parents);
		$parents = array_reverse($parents);
		$parentGedcomPlace = "2 PLAC ".trim(implode(',', $parents));

		return FunctionsPlaceUtils::getFirstLatLon($module, $fact, $parentGedcomPlace);
	}
	
	public static function updateOrder(ModuleInterface $moduleForPrefsOrder, Request $request) {		
		$order = (array)$request->get('order');		
		//set als preference
		$pref = implode(',',$order);		
		$moduleForPrefsOrder->setPreference('ORDER', $pref);
  }
  
	public static function accessibleModules(ModuleInterface $moduleForPrefsOrder, Tree $tree, UserInterface $user): Collection {
    return self::sort($moduleForPrefsOrder, app()
						->make(ModuleService::class)
						->findByComponent(FunctionsPlaceInterface::class, $tree, $user));
  }
  
	public static function modules(ModuleInterface $moduleForPrefsOrder, $include_disabled = false): Collection {
		return self::sort($moduleForPrefsOrder, app()
						->make(ModuleService::class)
						->findByInterface(FunctionsPlaceInterface::class, $include_disabled));
	}
	
  private static function sort(ModuleInterface $moduleForPrefsOrder, Collection $coll): Collection {
		$pref = $moduleForPrefsOrder->getPreference('ORDER');
		if ($pref === null) {
			$pref = '';
		}
		$order = explode(',',$pref);
		$order = array_flip($order);
						
		return $coll
						->map(function (FunctionsPlaceInterface $module) use ($order) {
							if (array_key_exists($module->name(), $order)) {
								$rank = $order[$module->name()];
								$module->setPlacesOrder($rank);
							}							
							return $module;
						})
						->sort(FunctionsPlaceUtils::sorter());
	}
	
	public static function sorter(): Closure {
		return function (FunctionsPlaceInterface $x, FunctionsPlaceInterface $y): int {
			return $x->getPlacesOrder() <=> $y->getPlacesOrder();
		};
	}
}
