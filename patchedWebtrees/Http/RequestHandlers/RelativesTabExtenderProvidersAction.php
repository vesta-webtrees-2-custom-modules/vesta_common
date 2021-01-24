<?php

namespace Cissee\WebtreesExt\Http\RequestHandlers;

use Fisharebest\Webtrees\Http\RequestHandlers\AbstractModuleComponentAction;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Services\TreeService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Vesta\Hook\HookInterfaces\RelativesTabExtenderInterface;
use Vesta\Hook\HookInterfaces\RelativesTabExtenderUtils;
use function app;
use function redirect;
use function route;

class RelativesTabExtenderProvidersAction extends AbstractModuleComponentAction {
  
  protected $module;
  
  public function __construct($module) {
    parent::__construct(app(ModuleService::class), app(TreeService::class));
    $this->module = $module;
  }
  
  public function handle(ServerRequestInterface $request): ResponseInterface {
    $this->updateStatus(RelativesTabExtenderInterface::class, $request);
    RelativesTabExtenderUtils::updateOrder($this->module, $request);
    $this->updateAccessLevel(RelativesTabExtenderInterface::class, $request);

    $url = route('module', [
        'module' => $this->module->name(),
        'action' => 'RelativesTabExtenderProviders'
    ]);

    return redirect($url);
  } 
}
