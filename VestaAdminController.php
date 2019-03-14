<?php

namespace Vesta;

use Cissee\WebtreesExt\AbstractModuleBaseController;
use Fisharebest\Webtrees\Tree;
use Symfony\Component\HttpFoundation\Response;

class VestaAdminController extends AbstractModuleBaseController {

  protected $layout = 'layouts/administration';

  public function __construct(string $moduleName) {
    //__DIR__ is the directory of VestaModuleTrait - that's intentional: it's a generic view!
    parent::__construct(__DIR__, $moduleName);
  }

  //adapted from ModuleController (e.g. listFooters)
  public function listHooks(
          $modules,
          $interface,
          $title,
          $description,
          $access,
          $sorting): Response {

    return $this->viewResponse('admin/vesta_components', [
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
