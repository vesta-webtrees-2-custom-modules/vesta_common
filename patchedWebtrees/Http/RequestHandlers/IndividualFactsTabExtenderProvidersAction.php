<?php

namespace Cissee\WebtreesExt\Http\RequestHandlers;

use Fisharebest\Webtrees\Http\RequestHandlers\AbstractModuleComponentAction;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Services\TreeService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Vesta\Hook\HookInterfaces\IndividualFactsTabExtenderInterface;
use Vesta\Hook\HookInterfaces\IndividualFactsTabExtenderUtils;
use function app;
use function redirect;
use function route;

class IndividualFactsTabExtenderProvidersAction extends AbstractModuleComponentAction {
  
  protected $module;
  
  public function __construct($module) {
    parent::__construct(app(ModuleService::class), app(TreeService::class));
    $this->module = $module;
  }
  
  public function handle(ServerRequestInterface $request): ResponseInterface {
    $this->updateStatus(IndividualFactsTabExtenderInterface::class, $request);
    IndividualFactsTabExtenderUtils::updateOrder($this->module, $request);
    $this->updateAccessLevel(IndividualFactsTabExtenderInterface::class, $request);

    $url = route('module', [
        'module' => $this->module->name(),
        'action' => 'IndividualFactsTabExtenderProviders'
    ]);

    return redirect($url);
  } 
}
