<?php

namespace Vesta\Hook\HookInterfaces;

use Cissee\WebtreesExt\Requests;
use Closure;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;
use Psr\Http\Message\ServerRequestInterface;
use Vesta\Model\GovReference;
use Vesta\Model\LocReference;
use Vesta\Model\MapCoordinates;
use Vesta\Model\PlaceStructure;
use Vesta\Model\Trace;
use function app;

class FunctionsPlaceUtils {

  public static function gov2html(ModuleInterface $module, Tree $tree, GovReference $gov): string {
    $functionsPlaceProviders = FunctionsPlaceUtils::accessibleModules($module, $tree, Auth::user())
            ->toArray();
    
    $ret = "";
    foreach ($functionsPlaceProviders as $functionsPlaceProvider) {      
      $html = $functionsPlaceProvider->gov2html($gov);
      if ($html !== null) {
        $ret .= $html;
      }      
    }
    return $ret;
  }

  //for now, never fallback via indirect parent hierarchies
  public static function plac2Map(ModuleInterface $module, PlaceStructure $ps, $fallbackViaParents = true): ?MapCoordinates {
    //1. via gedcom
    if (($ps->getLati() !== null) && ($ps->getLong() !== null)) {
      return new MapCoordinates($ps->getLati(), $ps->getLong(), new Trace(I18N::translate('map coordinates directly (MAP tag)')));
    }

    $functionsPlaceProviders = FunctionsPlaceUtils::accessibleModules($module, $ps->getTree(), Auth::user());
    
    //we have to try all ways:
    //more direct (e.g. plac2map) may have lower order than more indirect way.
    
    //the following is equivalent to (but hopefully more effective than)
    //
    //
    //set n = 1;
    //create sublist of n elements from $functionsPlaceProviders;
    //attempt to obtain map from sublist, return if successful
    //increment n and repeat
    
    $bestMap = null;
    $bestMapProviderIndex = count($functionsPlaceProviders);
    
    error_log($ps->getGedcomName());

    //2. via plac2map
    $index = 0;
    foreach ($functionsPlaceProviders as $functionsPlaceProvider) {      
      $map = $functionsPlaceProvider->plac2Map($ps);
      if ($map !== null) {
        //first one wins!
        $bestMap = $map;
        error_log("set bestMap 2.");
        $bestMapProviderIndex = $index;
        break;
      }
      $index++;
    }
    
    error_log("after plac2map: " . $bestMapProviderIndex);
    
    //3. via plac2loc + loc2map
    //any providers after current best are ignored!
    //if we have plac2map via 2, we don't care about e.g. plac2loc (via 3, or even via 2) + loc2map (via any)
            
    $index = 0;
    foreach ($functionsPlaceProviders as $functionsPlaceProvider) {
      if ($index >= $bestMapProviderIndex) {
        break;
      }
      $loc = $functionsPlaceProvider->plac2loc($ps);
      if ($loc !== null) {
        $index2 = 0;
        foreach ($functionsPlaceProviders as $functionsPlaceProvider2) {
          $maxIndex = max($index, $index2);
          if ($maxIndex >= $bestMapProviderIndex) {
            break; //only inner loop, we have to check other outers (we may be at (2,4), but (3,3) is overall preferable)
          }
              
          $map = $functionsPlaceProvider2->loc2map($loc);
          if ($map !== null) {
            error_log("set bestMap 3.");
            $bestMap = $map;
            $bestMapProviderIndex = $maxIndex;
            break; //only inner loop, we have to check other outers
          }            
          $index2++;
        }
      }
      $index++;
    }
    
    error_log("after plac2loc + loc2map: " . $bestMapProviderIndex);
    
    //4. via plac2gov + gov2map
    
    $index = 0;
    foreach ($functionsPlaceProviders as $functionsPlaceProvider) {
      if ($index >= $bestMapProviderIndex) {
        break;
      }
      $gov = $functionsPlaceProvider->plac2gov($ps);
      if ($gov !== null) {
        $index2 = 0;
        foreach ($functionsPlaceProviders as $functionsPlaceProvider2) {
          $maxIndex = max($index, $index2);
          if ($maxIndex >= $bestMapProviderIndex) {
            break; //only inner loop, we have to check other outers (we may be at (2,4), but (3,3) is overall preferable)
          }
          
          $map = $functionsPlaceProvider2->gov2map($gov);
          if ($map !== null) {
            error_log("set bestMap 4.");
            $bestMap = $map;
            $bestMapProviderIndex = $maxIndex;
            break; //only inner loop, we have to check other outers
          }
          $index2++;
        }
      }
      $index++;
    }    
    
    error_log("after plac2gov + gov2map: " . $bestMapProviderIndex);
    
    //5. via plac2loc + loc2gov + gov2map
    
    $index = 0;
    foreach ($functionsPlaceProviders as $functionsPlaceProvider) {
      if ($index >= $bestMapProviderIndex) {
        break;
      }
      $loc = $functionsPlaceProvider->plac2loc($ps);
      if ($loc !== null) {
        $index2 = 0;
        foreach ($functionsPlaceProviders as $functionsPlaceProvider2) {
          $maxIndex = max($index, $index2);
          if ($maxIndex >= $bestMapProviderIndex) {
            break;
          }
          $gov = $functionsPlaceProvider2->loc2gov($loc);
          if ($gov !== null) {
            $index3 = 0;
            foreach ($functionsPlaceProviders as $functionsPlaceProvider3) {
              $maxIndex = max($index, $index2, $index3);
              if ($maxIndex >= $bestMapProviderIndex) {
                break;
              }
              $map = $functionsPlaceProvider3->gov2map($gov);
              if ($map !== null) {
                error_log("set bestMap 5.");
                $bestMap = $map;
                $bestMapProviderIndex = $maxIndex;
                break;
              }            
              $index3++;
            }
          }
        }
        $index2++;
      }
      $index++;
    }
    
    error_log("after plac2loc + loc2gov + gov2map: " . $bestMapProviderIndex);
    error_log(print_r($bestMap, true));
    if ($bestMap !== null) {
      return $bestMap;
    }
    
    //6. via parent hierarchy?
    $gedcomPlace = $ps->getGedcom();
    if (!$fallbackViaParents || (strpos($gedcomPlace, ',') === false)) {
      return null;
    }
    
    $parents = explode(',', $gedcomPlace);
    $parents = array_reverse($parents);
    array_pop($parents);
    $parents = array_reverse($parents);
    $parentGedcomPlace = "2 PLAC " . trim(implode(',', $parents));

    $parentPs = new PlaceStructure($parentGedcomPlace, $ps->getTree(), $ps->getEventType(), $ps->getEventDateInterval());
    return FunctionsPlaceUtils::plac2Map($module, $parentPs);
  }
  
  //for now, never fallback via indirect parent hierarchies
  public static function plac2Gov(ModuleInterface $module, PlaceStructure $ps, $fallbackViaParents = true): ?GovReference {
    //_GOV is a non-standard tag - we don't know how to handle it directly!

    $functionsPlaceProviders = FunctionsPlaceUtils::accessibleModules($module, $ps->getTree(), Auth::user())
            ->toArray();
    
    //2. via plac2gov
    
    foreach ($functionsPlaceProviders as $functionsPlaceProvider) {
      $gov = $functionsPlaceProvider->plac2gov($ps);
      if ($gov !== null) {
        //first one wins!
        return $gov;
      }
    }
    
    //3. via plac2loc + loc2gov
    foreach ($functionsPlaceProviders as $functionsPlaceProvider) {
      $loc = $functionsPlaceProvider->plac2loc($ps);
      if ($loc !== null) {
        //first one (with a loc2gov) wins
        foreach ($functionsPlaceProviders as $functionsPlaceProvider2) {
          $gov = $functionsPlaceProvider2->loc2gov($loc);
          if ($gov !== null) {
            //first one wins!
            return $gov;
          }
        }
      }
    }
    
    //4. via parent hierarchy?
    $gedcomPlace = $ps->getGedcom();
    if (!$fallbackViaParents || (strpos($gedcomPlace, ',') === false)) {
      return null;
    }
    
    $parents = explode(',', $gedcomPlace);
    $parents = array_reverse($parents);
    array_pop($parents);
    $parents = array_reverse($parents);
    $parentGedcomPlace = "2 PLAC " . trim(implode(',', $parents));

    $parentPs = new PlaceStructure($parentGedcomPlace, $ps->getTree(), $ps->getEventType(), $ps->getEventDateInterval());
    return FunctionsPlaceUtils::plac2Gov($module, $parentPs);
  }
  
  //for now, never fallback via indirect parent hierarchies
  public static function plac2Loc(ModuleInterface $module, PlaceStructure $ps, $fallbackViaParents = true): ?LocReference {
    //_LOC is a non-standard tag - we don't know how to handle it directly!

    $functionsPlaceProviders = FunctionsPlaceUtils::accessibleModules($module, $ps->getTree(), Auth::user())
            ->toArray();
    
    //2. via loc2Gov
    
    foreach ($functionsPlaceProviders as $functionsPlaceProvider) {
      $loc = $functionsPlaceProvider->plac2Loc($ps);
      if ($loc !== null) {
        //first one wins!
        return $loc;
      }
    }
    
    //3. via parent hierarchy?
    $gedcomPlace = $ps->getGedcom();
    if (!$fallbackViaParents || (strpos($gedcomPlace, ',') === false)) {
      return null;
    }
    
    $parents = explode(',', $gedcomPlace);
    $parents = array_reverse($parents);
    array_pop($parents);
    $parents = array_reverse($parents);
    $parentGedcomPlace = "2 PLAC " . trim(implode(',', $parents));

    $parentPs = new PlaceStructure($parentGedcomPlace, $ps->getTree(), $ps->getEventType(), $ps->getEventDateInterval());
    return FunctionsPlaceUtils::plac2Loc($module, $parentPs);
  }
    
  //for now, never fallback via indirect parent hierarchies
  public static function loc2Map(ModuleInterface $module, LocReference $loc): ?MapCoordinates {
    $functionsPlaceProviders = FunctionsPlaceUtils::accessibleModules($module, $loc->getTree(), Auth::user())
            ->toArray();
    
    //3. via loc2Map
    
    foreach ($functionsPlaceProviders as $functionsPlaceProvider) {
      $map = $functionsPlaceProvider->loc2Map($loc);
      if ($map !== null) {
        //first one wins!
        return $map;
      }
    }
    
    //4. via loc2gov + gov2map
    foreach ($functionsPlaceProviders as $functionsPlaceProvider) {
      $gov = $functionsPlaceProvider->loc2gov($loc);
      if ($gov !== null) {
        //first one (with a gov2map) wins
        foreach ($functionsPlaceProviders as $functionsPlaceProvider2) {
          $map = $functionsPlaceProvider2->gov2map($gov);
          if ($map !== null) {
            //first one wins!
            return $map;
          }
        }
      }
    }

    return null;
  }

  public static function updateOrder(ModuleInterface $moduleForPrefsOrder, ServerRequestInterface $request) {
    $order = Requests::getArray($request, 'order');
    //set als preference
    $pref = implode(',', $order);
    $moduleForPrefsOrder->setPreference('ORDER_PLACE_FUNCTIONS', $pref);
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
    $pref = $moduleForPrefsOrder->getPreference('ORDER_PLACE_FUNCTIONS');
    if ($pref === null) {
      $pref = '';
    }
    $order = explode(',', $pref);
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
  
  //legacy stuff
  
  public static function getParentPlaces($module, PlaceStructure $place, $typesOfLocation, $recursively = false) {
    $functionsPlaceProviders = FunctionsPlaceUtils::accessibleModules($module, $place->getTree(), Auth::user())
            ->toArray();
    
    $places = [];
    foreach ($functionsPlaceProviders as $functionsPlaceProvider) {
      $parentPlaces = $functionsPlaceProvider->hPlacesGetParentPlaces($place, $typesOfLocation, $recursively);
      array_merge($places, $parentPlaces);
    }
    
    return $places;
  }
}
