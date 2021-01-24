<?php

namespace Cissee\WebtreesExt\Http\RequestHandlers;

use Fisharebest\Webtrees\Http\RequestHandlers\AbstractModuleComponentAction;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Services\TreeService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Vesta\Hook\HookInterfaces\FunctionsPlaceInterface;
use Vesta\Hook\HookInterfaces\FunctionsPlaceUtils;
use function app;
use function redirect;
use function route;

class FunctionsPlaceProvidersAction extends AbstractModuleComponentAction {
  
  protected $module;
  
  public function __construct($module) {
    parent::__construct(app(ModuleService::class), app(TreeService::class));
    $this->module = $module;
  }
  
  public function handle(ServerRequestInterface $request): ResponseInterface {
    $this->updateStatus(FunctionsPlaceInterface::class, $request);
    FunctionsPlaceUtils::updateOrder($this->module, $request);
    $this->updateAccessLevel(FunctionsPlaceInterface::class, $request);

    $url = route('module', [
        'module' => $this->module->name(),
        'action' => 'FunctionsPlaceProviders'
    ]);

    return redirect($url);
  } 
}
