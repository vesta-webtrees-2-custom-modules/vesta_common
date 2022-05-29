<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Http\RequestHandlers;

use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Services\TreeService;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

//cf ModuleServiceExt
abstract class AbstractModuleSpecificComponentAction implements RequestHandlerInterface
{
    protected ModuleService $module_service;

    protected TreeService $tree_service;

    /**
     * @param ModuleService $module_service
     * @param TreeService   $tree_service
     */
    public function __construct(
        ModuleService $module_service, 
        TreeService $tree_service)
    {
        $this->module_service = $module_service;
        $this->tree_service   = $tree_service;
    }

    /**
     * Update the access levels of the modules.
     * (per-target, different from original AbstractModuleComponentAction)
     *
     * @param string                 $interface
     * @param string                 $interfaceForAccessLevel
     * @param ServerRequestInterface $request
     *
     * @return void
     */
    protected function updateSpecificAccessLevel(
        string $interface, 
        string $interfaceForAccessLevel,
        ServerRequestInterface $request): void
        
    {
        $modules = $this->module_service->findByInterface($interface, true);

        $params = (array) $request->getParsedBody();

        $trees = $this->tree_service->all();

        foreach ($modules as $module) {
            foreach ($trees as $tree) {
                $key          = 'access-' . $module->name() . '-' . $tree->id();
                $access_level = (int) ($params[$key] ?? 0);

                //[RC] adjusted
                if ($access_level !== $module->accessLevel($tree, $interfaceForAccessLevel)) {
                    DB::table('module_privacy')->updateOrInsert([
                        'module_name' => $module->name(),
                        'gedcom_id'   => $tree->id(),
                        //[RC] adjusted
                        'interface'   => $interfaceForAccessLevel,
                    ], [
                        'access_level' => $access_level,
                    ]);
                }
            }
        }
    }
}
