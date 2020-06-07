<?php

namespace Vesta\Hook\HookInterfaces;

use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;
use Vesta\Model\GedcomDateInterval;
use Vesta\Model\GenericViewElement;
use Vesta\Model\GovReference;
use Vesta\Model\LocReference;
use Vesta\Model\MapCoordinates;
use Vesta\Model\PlaceStructure;

/**
 * base impl of FunctionsPlaceInterface
 */
trait EmptyFunctionsPlace {

  protected $placesOrder = 0;

  public function setPlacesOrder(int $order): void {
    $this->placesOrder = $order;
  }

  public function getPlacesOrder(): int {
    return $this->placesOrder ?? $this->defaultPlacesOrder();
  }

  public function defaultPlacesOrder(): int {
    return 9999;
  }

  ////////////////////////////////////////////////////////////////////////////////
  
  public function plac2map(PlaceStructure $ps): ?MapCoordinates {
    return null;
  }
  
  public function plac2html(PlaceStructure $ps): ?GenericViewElement {
    return null;
  }
  
  public function plac2loc(PlaceStructure $ps): ?LocReference {
    return null;
  }
  
  public function plac2gov(PlaceStructure $ps): ?GovReference {
    return null;
  }
  
  public function loc2map(LocReference $loc): ?MapCoordinates {
    return null;
  }
    
  public function loc2gov(LocReference $loc): ?GovReference {
    return null;
  }
  
  public function gov2map(GovReference $gov): ?MapCoordinates {
    return null;
  }

  public function gov2html(GovReference $gov, Tree $tree): ?GenericViewElement {
    return null;
  }

  public function map2html(MapCoordinates $map): ?GenericViewElement {
    return null;
  }
  
  public function gov2plac(GovReference $gov, Tree $tree): ?PlaceStructure {
    return null;
  }
  
  public function gov2loc(GovReference $gov, Tree $tree): ?LocReference {
    return null;
  }
  
  public function loc2plac(LocReference $loc): ?PlaceStructure {
    return null;
  }

  public function loc2linkIcon(LocReference $loc): ?string {
    return null;
  }

  public function govPgov(GovReference $gov, GedcomDateInterval $dateInterval, Collection $typesOfLocation, int $maxLevels = PHP_INT_MAX): Collection {
    return new Collection();
  }
}
