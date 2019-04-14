<?php

namespace Vesta;

use Fisharebest\Webtrees\Http\Controllers\AbstractBaseController;
use Fisharebest\Webtrees\Tree;
use Psr\Http\Message\ResponseInterface;
use function route;

class VestaAdminController extends AbstractBaseController {

  protected $layout = 'layouts/administration';

  public static function vestaViewsNamespace(): string {
    return 'Vesta_Views_Namespace';
  }

  protected $moduleName;

  public function __construct(string $moduleName) {
    $this->moduleName = $moduleName;
  }

  //adapted from ModuleController (e.g. listFooters)
  public function listHooks(
          $modules,
          $interface,
          $title,
          $description,
          $access,
          $sorting): ResponseInterface {

    //assumes the namespace has been registered!
    return $this->viewResponse(VestaAdminController::vestaViewsNamespace() . '::admin/vesta_components', [
                'interface' => $interface,
                'description' => $description,
                'modules' => $modules,
                'title' => $title,
                'trees' => Tree::all(),
                'uses_access' => $access,
                'uses_sorting' => $sorting,
                'cancelRoute' => route('module', [
                    'module' => $this->moduleName,
                    'action' => 'Admin'
                ])
    ]);
  }

}
