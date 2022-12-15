<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Module;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Fisharebest\Webtrees\Services\ModuleService;
use Illuminate\Support\Collection;
use function app;

class ModuleMetaInterfaceUtils {

    public static function getModulesOutOfRange(string $targetWebtreesVersion, bool $updateable): Collection {
        $providers = ModuleMetaInterfaceUtils::accessibleModules(Auth::user());

        return $providers
                ->filter(function (ModuleInterface $module) use ($targetWebtreesVersion, $updateable): bool {
                    $currentMeta = $module->customModuleMetaData();
                    //cannot use string comparator, we need alphanumeric comparator within the version parts,
                    
                    $isInRange = 
                        (($currentMeta !== null) && 
                        (version_compare($currentMeta->minRequiredWebtreesVersion(),$targetWebtreesVersion) < 1) && 
                        (version_compare($targetWebtreesVersion,$currentMeta->minUnsupportedWebtreesVersion()) < 0));
                        //($currentMeta->minRequiredWebtreesVersion() <= $targetWebtreesVersion) && 
                        //($targetWebtreesVersion < $currentMeta->minUnsupportedWebtreesVersion()));

                    if (!$isInRange) {
                        //is is updateable?
                        $targetMeta = $module->customModuleMetaData($targetWebtreesVersion);
                        $isUpdateable = 
                            (($targetMeta !== null) && 
                            (version_compare($targetMeta->minRequiredWebtreesVersion(), $targetWebtreesVersion) < 1) && 
                            (version_compare($targetWebtreesVersion, $targetMeta->minUnsupportedWebtreesVersion()) < 0));
                            //($targetMeta->minRequiredWebtreesVersion() <= $targetWebtreesVersion) && 
                            //($targetWebtreesVersion < $targetMeta->minUnsupportedWebtreesVersion()));
                        
                        return ($isUpdateable === $updateable);
                    }

                    return false;
                });
    }

    public static function accessibleModules(UserInterface $user): Collection {
        if (!Auth::isAdmin($user)) {
            return new Collection();
        }

        return app()
                ->make(ModuleService::class)
                ->findByInterface(ModuleMetaInterface::class, false, true);
    }

}
