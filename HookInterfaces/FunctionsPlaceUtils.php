<?php

namespace Vesta\Hook\HookInterfaces;

use Cissee\WebtreesExt\Requests;
use Closure;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;
use Psr\Http\Message\ServerRequestInterface;
use Vesta\Model\GedcomDateInterval;
use Vesta\Model\GenericViewElement;
use Vesta\Model\GovReference;
use Vesta\Model\LocReference;
use Vesta\Model\MapCoordinates;
use Vesta\Model\PlaceStructure;
use Vesta\Model\Trace;
use function app;

class FunctionsPlaceUtils {
  
  public static function loc2linkIcon(ModuleInterface $module, LocReference $loc): string {
    $functionsPlaceProviders = FunctionsPlaceUtils::accessibleModulesPrintFunctions($module, $loc->getTree(), Auth::user())
            ->toArray();
    
    $links = array();
    foreach ($functionsPlaceProviders as $functionsPlaceProvider) {      
      $link = $functionsPlaceProvider->loc2linkIcon($loc);
      if ($link !== null) {
        $links[] = $link;
      }      
    }
    return implode($links);
  }
  
  public static function plac2html(ModuleInterface $module, PlaceStructure $ps): GenericViewElement {
    $functionsPlaceProviders = FunctionsPlaceUtils::accessibleModulesPrintFunctions($module, $ps->getTree(), Auth::user())
            ->toArray();
    
    $gves = array();
    foreach ($functionsPlaceProviders as $functionsPlaceProvider) {      
      $gve = $functionsPlaceProvider->plac2html($ps);
      if ($gve !== null) {
        $gves[] = $gve;
      }      
    }
    return GenericViewElement::implode($gves);
  }
  
  public static function gov2html(ModuleInterface $module, Tree $tree, GovReference $gov): GenericViewElement {
    $functionsPlaceProviders = FunctionsPlaceUtils::accessibleModulesPrintFunctions($module, $tree, Auth::user())
            ->toArray();
    
    $gves = array();
    foreach ($functionsPlaceProviders as $functionsPlaceProvider) {      
      $gve = $functionsPlaceProvider->gov2html($gov, $tree);
      if ($gve !== null) {
        $gves[] = $gve;
      }      
    }
    return GenericViewElement::implode($gves);
  }
  
  public static function map2html(ModuleInterface $module, Tree $tree, MapCoordinates $map): GenericViewElement {
    $functionsPlaceProviders = FunctionsPlaceUtils::accessibleModulesPrintFunctions($module, $tree, Auth::user())
            ->toArray();
    
    $gves = array();
    foreach ($functionsPlaceProviders as $functionsPlaceProvider) {      
      $gve = $functionsPlaceProvider->map2html($map);
      if ($gve !== null) {
        $gves[] = $gve;
      }      
    }
    return GenericViewElement::implode($gves);
  }
  
  //for now, never fallback via indirect parent hierarchies
  public static function plac2map(ModuleInterface $module, PlaceStructure $ps, $fallbackViaParents = true): ?MapCoordinates {
   
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

    //error_log($ps->getGedcomName());
    //
    //2. via plac2map
    $index = 0;
    foreach ($functionsPlaceProviders as $functionsPlaceProvider) {      
      $map = $functionsPlaceProvider->plac2map($ps);
      if ($map !== null) {
        //first one wins!
        $bestMap = $map;
        //error_log("set bestMap 2.");
        $bestMapProviderIndex = $index;
        break;
      }
      $index++;
    }

    //error_log(microtime() . "after plac2map: " . $bestMapProviderIndex);

    //3. via plac2loc + loc2map
    //any providers after current best are ignored!
    //if we have plac2map via 2, we don't care about e.g. plac2loc (via 3, or even via 2) + loc2map (via any)

    $index = 0;
    foreach ($functionsPlaceProviders as $functionsPlaceProvider) {
      if ($index >= $bestMapProviderIndex) {
        break;
      }
      $loc = $functionsPlaceProvider->plac2loc($ps);
      //error_log(microtime() . "loc" . ($loc != null));
      if ($loc !== null) {
        $index2 = 0;
        foreach ($functionsPlaceProviders as $functionsPlaceProvider2) {
          $maxIndex = max($index, $index2);
          if ($maxIndex >= $bestMapProviderIndex) {
            break; //only inner loop, we have to check other outers (we may be at (2,4), but (3,3) is overall preferable)
          }

          $map = $functionsPlaceProvider2->loc2map($loc);
          //error_log(microtime() . "map" . ($map != null));
          if ($map !== null) {
            //error_log("set bestMap 3.");
            $bestMap = $map;
            $bestMapProviderIndex = $maxIndex;
            break; //only inner loop, we have to check other outers
          }            
          $index2++;
        }
      }
      $index++;
    }

    //error_log("after plac2loc + loc2map: " . $bestMapProviderIndex);

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
            //error_log("set bestMap 4.");
            $bestMap = $map;
            $bestMapProviderIndex = $maxIndex;
            break; //only inner loop, we have to check other outers
          }
          $index2++;
        }
      }
      $index++;
    }    

    //error_log("after plac2gov + gov2map: " . $bestMapProviderIndex);

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
                //error_log("set bestMap 5.");
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

    //error_log("after plac2loc + loc2gov + gov2map: " . $bestMapProviderIndex);
    //error_log(print_r($bestMap, true));
    if ($bestMap !== null) {
      return $bestMap;
    }

    //6. via parent hierarchy?
    if (!$fallbackViaParents) {
      return null;
    }    
    $parentPs = $ps->parent();
    if ($parentPs === null) {
      return null;
    }
    return FunctionsPlaceUtils::plac2map($module, $parentPs, true);
  }
  
  //for now, never fallback via indirect parent hierarchies
  public static function plac2gov(ModuleInterface $module, PlaceStructure $ps, $fallbackViaParents = true): ?GovReference {    
    //1. skip:
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
    if (!$fallbackViaParents) {
      return null;
    }    
    $parentPs = $ps->parent();
    if ($parentPs === null) {
      return null;
    }
    return FunctionsPlaceUtils::plac2gov($module, $parentPs, true);
  }
  
  //for now, never fallback via indirect parent hierarchies
  public static function plac2loc(ModuleInterface $module, PlaceStructure $ps, $fallbackViaParents = true): ?LocReference {
    //1. skip:
    //_LOC is a non-standard tag - we don't know how to handle it directly!

    $functionsPlaceProviders = FunctionsPlaceUtils::accessibleModules($module, $ps->getTree(), Auth::user())
            ->toArray();
    
    //2. via plac2loc
    
    foreach ($functionsPlaceProviders as $functionsPlaceProvider) {
      $loc = $functionsPlaceProvider->plac2loc($ps);
      if ($loc !== null) {
        //first one wins!
        return $loc;
      }
    }
    
    //3. via parent hierarchy?
    if (!$fallbackViaParents) {
      return null;
    }    
    $parentPs = $ps->parent();
    if ($parentPs === null) {
      return null;
    }
    return FunctionsPlaceUtils::plac2loc($module, $parentPs, true);
  }
    
  //for now, never fallback via indirect parent hierarchies
  public static function loc2map(ModuleInterface $module, LocReference $loc): ?MapCoordinates {
    $functionsPlaceProviders = FunctionsPlaceUtils::accessibleModules($module, $loc->getTree(), Auth::user())
            ->toArray();
    
    //3. via loc2map
    
    foreach ($functionsPlaceProviders as $functionsPlaceProvider) {
      $map = $functionsPlaceProvider->loc2map($loc);
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
     
  public static function gov2plac(ModuleInterface $module, GovReference $gov, Tree $tree): ?PlaceStructure {
    //Issue #54
    //expensive, therefore cached
    return Registry::cache()->array()->remember(FunctionsPlaceUtils::class . 'gov2plac_' . $gov->getId(), static function () use ($module, $gov, $tree): ?PlaceStructure {
      
      $functionsPlaceProviders = FunctionsPlaceUtils::accessibleModules($module, $tree, Auth::user())
            ->toArray();
    
      //1. via gov2plac

      foreach ($functionsPlaceProviders as $functionsPlaceProvider) {
        $ps = $functionsPlaceProvider->gov2plac($gov, $tree);
        if ($ps !== null) {
          //first one wins!
          return $ps;
        }
      }

      //2. via gov2loc + loc2plac
      foreach ($functionsPlaceProviders as $functionsPlaceProvider) {
        $loc = $functionsPlaceProvider->gov2loc($gov, $tree);
        if ($loc !== null) {
          //first one (with a loc2plac) wins
          foreach ($functionsPlaceProviders as $functionsPlaceProvider2) {
            $ps = $functionsPlaceProvider2->loc2plac($loc);
            if ($ps !== null) {
              //first one wins!
              return $ps;
            }
          }
        }
      }

      return null;
    });
  }
     
  ////////////////////////////////////////////////////////////////////////////////

  public static function placPplac(
          ModuleInterface $module, 
          PlaceStructure $ps, 
          Collection $typesOfLocation, 
          int $maxLevels = PHP_INT_MAX): Collection {
    
    $ret = new Collection();
    
    if ($maxLevels < 1) {
      return $ret;
    }
    
    //1. directly
    $parentPs = $ps->parent();
    if ($parentPs !== null) {
      $ret->add($parentPs);
      
      //also check parent recursively
      $ret = $ret->merge(FunctionsPlaceUtils::placPplac($module, $parentPs, $typesOfLocation, $maxLevels-1));
    }
    
    //2. via any plac2gov + govPgov + any gov2Plac
    $gov = FunctionsPlaceUtils::plac2gov($module, $ps, false); //using $level from $ps!
    if ($gov !== null) {
      $parentGovs = FunctionsPlaceUtils::govPgov($module, $ps->getTree(), $gov, $ps->getEventDateInterval(), $typesOfLocation, $maxLevels);
      
      foreach ($parentGovs as $parentGov) {
        $parentPsViaGov = FunctionsPlaceUtils::gov2plac($module, $parentGov, $ps->getTree()); //using $level from $parentGov!
        if ($parentPsViaGov !== null) {
          $ret->add($parentPsViaGov);
        }
      }
    }
    
    //3. via any plac2loc + locPloc + loc2plac
    $loc = FunctionsPlaceUtils::plac2loc($module, $ps, false);
    if ($loc !== null) {
      $parentLocs = FunctionsPlaceUtils::locPloc($module, $ps->getTree(), $loc, $ps->getEventDateInterval(), $typesOfLocation, $maxLevels);
      
      $functionsPlaceProviders = FunctionsPlaceUtils::accessibleModules($module, $ps->getTree(), Auth::user())
        ->toArray();
    
      foreach ($parentLocs as $parentLoc) {
        foreach ($functionsPlaceProviders as $functionsPlaceProvider) {      
          $parentPsViaLoc = $functionsPlaceProvider->loc2plac($parentLoc);
          if ($parentPsViaLoc !== null) {
            $ret->add($parentPsViaLoc);
          }
        }
      }
    }
    
    return $ret;
  }
 
  public static function govPgov(
          ModuleInterface $module, 
          Tree $tree, 
          GovReference $gov, 
          GedcomDateInterval $dateInterval, 
          Collection $typesOfLocation, 
          int $maxLevels = PHP_INT_MAX): Collection {
    
    $functionsPlaceProviders = FunctionsPlaceUtils::accessibleModules($module, $tree, Auth::user())
            ->toArray();
    
    $ret = new Collection();
    foreach ($functionsPlaceProviders as $functionsPlaceProvider) {      
      $parentGovs = $functionsPlaceProvider->govPgov($gov, $dateInterval, $typesOfLocation, $maxLevels);
      $ret = $ret->merge($parentGovs);
    }
    
    return $ret;
  }
  
  public static function locPloc(
          ModuleInterface $module, 
          Tree $tree, 
          LocReference $loc, 
          GedcomDateInterval $dateInterval, 
          Collection $typesOfLocation, 
          int $maxLevels = PHP_INT_MAX): Collection {
    
    $functionsPlaceProviders = FunctionsPlaceUtils::accessibleModules($module, $tree, Auth::user())
            ->toArray();
    
    $ret = new Collection();
    foreach ($functionsPlaceProviders as $functionsPlaceProvider) {      
      $parentLocs = $functionsPlaceProvider->locPloc($loc, $dateInterval, $typesOfLocation, $maxLevels);
      $ret = $ret->merge($parentLocs);
    }
    
    return $ret;
  }
  
  ////////////////////////////////////////////////////////////////////////////////
  
  public static function updateOrder(ModuleInterface $moduleForPrefsOrder, ServerRequestInterface $request) {
    $order = Requests::getArray($request, 'order');
    //set als preference
    $pref = implode(',', $order);
    $moduleForPrefsOrder->setPreference('ORDER_PLACE_FUNCTIONS', $pref);
  }

  public static function accessibleModules(
          ModuleInterface $moduleForPrefsOrder, 
          Tree $tree, 
          UserInterface $user): Collection {
    
    return self::sort($moduleForPrefsOrder, app()->make(ModuleService::class)
            ->findByComponent(FunctionsPlaceInterface::class, $tree, $user));
  }

  public static function accessibleModulesPrintFunctions(
          ModuleInterface $moduleForPrefsOrder, 
          Tree $tree, 
          UserInterface $user): Collection {
    
    //currently no sorting!
    return app()->make(ModuleService::class)
            ->findByComponent(PrintFunctionsPlaceInterface::class, $tree, $user);
  }
  
  public static function modules(
          ModuleInterface $moduleForPrefsOrder, 
          $include_disabled = false): Collection {
    
    return self::sort($moduleForPrefsOrder, app()
                            ->make(ModuleService::class)
                            ->findByInterface(FunctionsPlaceInterface::class, $include_disabled));
  }

  private static function sort(
          ModuleInterface $moduleForPrefsOrder, 
          Collection $coll): Collection {
    
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
}
