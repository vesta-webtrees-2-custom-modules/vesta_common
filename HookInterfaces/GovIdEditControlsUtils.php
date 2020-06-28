<?php

namespace Vesta\Hook\HookInterfaces;

use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;
use function app;

class GovIdEditControlsUtils {

  public static function accessibleModules(Tree $tree, UserInterface $user): Collection {
    return app()
                            ->make(ModuleService::class)
                            ->findByComponent(GovIdEditControlsInterface::class, $tree, $user);
  }

  public static function modules($include_disabled = false): Collection {
    return app()
                            ->make(ModuleService::class)
                            ->findByInterface(GovIdEditControlsInterface::class, $include_disabled);
  }

  
}
