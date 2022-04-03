<?php

namespace Vesta;

use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Services\TreeService;
use Fisharebest\Webtrees\Webtrees;
use Psr\Http\Message\ResponseInterface;
use function app;
use function route;

class VestaAdminController {

    use ViewResponseTrait;

    protected $moduleName;

    public function __construct(string $moduleName) {
        $this->moduleName = $moduleName;
        $this->layout = 'layouts/administration';
    }

    //adapted from ModuleController (e.g. listFooters)
    public function listHooks(
        $modules,
        $interface,
        $title,
        $description,
        $access,
        $sorting): ResponseInterface {

        $view = VestaUtils::vestaViewsNamespace() . '::admin/vesta_components';
        if (str_starts_with(Webtrees::VERSION, '2.1')) {
            $view = VestaUtils::vestaViewsNamespace() . '::admin/vesta_components';
        } else {
            $view = VestaUtils::vestaViewsNamespace() . '::admin/vesta_components_20';
        }      
      
        //assumes the namespace has been registered!
        return $this->viewResponse($view, [
                'interface' => $interface,
                'description' => $description,
                'modules' => $modules,
                'title' => $title,
                'trees' => app(TreeService::class)->all(),
                'uses_access' => $access,
                'uses_sorting' => $sorting,
                'cancelRoute' => route('module', [
                    'module' => $this->moduleName,
                    'action' => 'Admin'
                ])
        ]);
    }
}
