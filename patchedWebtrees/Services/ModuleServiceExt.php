<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Services;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;

//cf AbstractModuleSpecificComponentAction
class ModuleServiceExt
{
    /**
     * Modules which (a) provide a specific function and (b) we have permission to see
     * in a specific context.
     *
     * @param string        $interface
     * @param string        $interfaceForAccessLevel
     * @param Tree          $tree
     * @param UserInterface $user
     *
     * @return Collection<string,ModuleInterface>
     */
    public static function findBySpecificComponent(
        string $interface,
        string $interfaceForAccessLevel,
        Tree $tree,
        UserInterface $user): Collection
    {
        return \Vesta\VestaUtils::get(ModuleService::class)->findByInterface($interface, false, true)
            ->filter(static function (ModuleInterface $module) use ($interfaceForAccessLevel, $tree, $user): bool {
                return $module->accessLevel($tree, $interfaceForAccessLevel) >= Auth::accessLevel($tree, $user);
            });
    }
}
