<?php

namespace Vesta\Hook\HookInterfaces;

use Aura\Router\Route;
use Fisharebest\Webtrees\GedcomRecord;
use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;

interface FunctionsClippingsCartInterface {

  public function getAddToClippingsCartRoute(Route $route, Tree $tree): ?string;
  
  public function getDirectLinkTypes(): Collection;
  
  public function getIndirectLinks(GedcomRecord $record): Collection;
}
