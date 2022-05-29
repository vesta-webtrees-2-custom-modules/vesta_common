<?php

namespace Vesta;

use Cissee\WebtreesExt\MoreI18N;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Http\RequestHandlers\ControlPanel;
use Fisharebest\Webtrees\Http\RequestHandlers\ModulesAllPage;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Fisharebest\Webtrees\Services\TreeService;
use Fisharebest\Webtrees\Tree;
use Psr\Http\Message\ResponseInterface;
use function app;
use function route;

class VestaAdminController {

    use ViewResponseTrait;

    protected ModuleInterface $module;

    public function __construct(ModuleInterface $module) {
        $this->module = $module;
        $this->layout = 'layouts/administration';
    }

    //adapted from ModuleController (e.g. listFooters)
    public function listHooks(
        $modules,
        string $interface,
        string $title,
        string $description,
        bool $uses_access,
        bool $uses_sorting,
        bool $specific = false): ResponseInterface {

        $view = VestaUtils::vestaViewsNamespace() . '::admin/vesta_components';
      
        //cf AbstractModuleComponentPage
        $access_summary = $modules
            ->mapWithKeys(function (ModuleInterface $module) use ($interface): array {
                $access_levels = app(TreeService::class)->all()
                    ->map(static function (Tree $tree) use ($interface, $module): int {
                        return $module->accessLevel($tree, $interface);
                    })
                    ->uniqueStrict()
                    ->values()
                    ->map(static function (int $level): string {
                        return Auth::accessLevelNames()[$level];
                    })
                    ->all();

                return [$module->name() => $access_levels];
            })
            ->all();
            
        $breadcrumbs = [];
        $breadcrumbs[route(ControlPanel::class)] = MoreI18N::xlate('Control panel');
        $breadcrumbs[route(ModulesAllPage::class)] = MoreI18N::xlate('Modules');
        if ($specific) {
            $breadcrumbs[$this->module->getConfigLink()] = $this->module->title();
        }
        $breadcrumbs[] = $title;
        
        //assumes the namespace has been registered!
        return $this->viewResponse($view, [
                'description' => $description,
                'interface' => $interface,
                'modules' => $modules,
                'title' => $title,
                'trees' => app(TreeService::class)->all(),
                'uses_access' => $uses_access,
                'uses_sorting' => $uses_sorting,
                'access_summary' => $access_summary,
            
                'uses_enabled' => !$specific,
                'breadcrumbs' => $breadcrumbs,
                'cancelRoute' => route('module', [
                    'module' => $this->module->name(),
                    'action' => 'Admin'
                ]),
        ]);
    }
}
