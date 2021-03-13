<?php

namespace Vesta\Hook\HookInterfaces;

use Fisharebest\Webtrees\GedcomRecord;
use Fisharebest\Webtrees\Location;
use Illuminate\Support\Collection;

interface FunctionsClippingsCartInterface {
  
  public function getAddLocationActionAdditionalOptions(Location $location): ?array;
  
  public function postAddLocationActionHandleOption(ClippingsCartAddToCartInterface $target, Location $location, string $option): bool;
  
  /**
   * 
   * @param GedcomRecord $record
   * @return Collection of xref
   */
  public function getIndirectLocations(GedcomRecord $record): Collection;
  
  /*
  public function getAddToClippingsCartRoute(Route $route, Tree $tree): ?string;
  
  public function getDirectLinkTypes(): Collection;
  
  public function getIndirectLinks(GedcomRecord $record): Collection;
  
  public function getTransitiveLinks(GedcomRecord $record): Collection;  
  */
}
