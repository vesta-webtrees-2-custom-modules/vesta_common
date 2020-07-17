<?php

namespace Vesta\Hook\HookInterfaces;

use Aura\Router\Route;
use Fisharebest\Webtrees\GedcomRecord;
use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;

interface FunctionsClippingsCartInterface {

  public function getAddToClippingsCartRoute(Route $route, Tree $tree): ?string;
  
  /**
   * 
   * @return Collection<string>
   */
  public function getDirectLinkTypes(): Collection;
  
  /**
   * 
   * @param GedcomRecord $record
   * @return Collection<string> xrefs
   */
  public function getIndirectLinks(GedcomRecord $record): Collection;
  
  /**
   * 
   * @param string $xref
   * @return Collection<string> xrefs
   */
  public function getTransitiveLinks(GedcomRecord $record): Collection;
}
