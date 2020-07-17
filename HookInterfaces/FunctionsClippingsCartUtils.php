<?php

namespace Vesta\Hook\HookInterfaces;

use Aura\Router\Route;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\GedcomRecord;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;
use function app;

class FunctionsClippingsCartUtils {
    
  public static function getAddToClippingsCartRoute(ModuleInterface $module, Route $route, Tree $tree): ?string {
    $providers = FunctionsClippingsCartUtils::accessibleModules($tree, Auth::user())
            ->toArray();
    
    foreach ($providers as $provider) {      
      $ret = $provider->getAddToClippingsCartRoute($route, $tree);
      //first one wins!
      if ($ret !== null) {
        return $ret;
      }      
    }
    return null;
  }
  
  public static function getDirectLinkTypes(ModuleInterface $module, Tree $tree): Collection {
    $providers = FunctionsClippingsCartUtils::accessibleModules($tree, Auth::user())
            ->toArray();
    
    $ret = new Collection();
    foreach ($providers as $provider) {      
      $ret = $ret->merge($provider->getDirectLinkTypes());
    }
    return $ret;
  }
  
  public static function getIndirectLinks(ModuleInterface $module, GedcomRecord $record): Collection {
    $providers = FunctionsClippingsCartUtils::accessibleModules($record->tree(), Auth::user())
            ->toArray();
    
    $ret = new Collection();
    foreach ($providers as $provider) {      
      $ret = $ret->merge($provider->getIndirectLinks($record));
    }
    return $ret;
  }
  
  public static function getTransitiveLinks(ModuleInterface $module, GedcomRecord $record): Collection {
    $providers = FunctionsClippingsCartUtils::accessibleModules($record->tree(), Auth::user())
            ->toArray();
    
    $ret = new Collection();
    foreach ($providers as $provider) {      
      $ret = $ret->merge($provider->getTransitiveLinks($record));
    }
    return $ret;
  }
  
  public static function accessibleModules(Tree $tree, UserInterface $user): Collection {
    return app()
        ->make(ModuleService::class)
        ->findByComponent(FunctionsClippingsCartInterface::class, $tree, $user);
  }
}
