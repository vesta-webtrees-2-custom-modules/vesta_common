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
 * Hooks for additional functions on places
 * 
 * contract for all x2z, xPx functions:
 * impls must not traverse the place hierarchy (given or implied) in any way,
 * nor attempt to aggregate via other x2y, y2z functions themselves.
 * 
 */
interface FunctionsPlaceInterface {

  public function setPlacesOrder(int $order): void;

  public function getPlacesOrder(): int;

  public function defaultPlacesOrder(): int;

  ////////////////////////////////////////////////////////////////////////////////
  
  public function plac2map(PlaceStructure $ps): ?MapCoordinates;
  
  public function plac2html(PlaceStructure $ps): ?GenericViewElement;
  
  public function plac2loc(PlaceStructure $ps): ?LocReference;

  public function plac2gov(PlaceStructure $ps): ?GovReference;
  
  public function loc2map(LocReference $loc): ?MapCoordinates;
  
  public function loc2gov(LocReference $loc): ?GovReference;
  
  public function gov2map(GovReference $gov): ?MapCoordinates;
  
  public function gov2html(GovReference $gov): ?GenericViewElement;

  public function map2html(MapCoordinates $map): ?GenericViewElement;
  
  public function gov2plac(GovReference $gov, Tree $tree): ?PlaceStructure;
  
  public function gov2loc(GovReference $gov, Tree $tree): ?LocReference;
  
  public function loc2plac(LocReference $loc): ?PlaceStructure;

  /**
   * get parent(s) of indicated types ("POLI","RELI" etc)
   * 
   * @param GovReference $gov
   * @param GedcomDateInterval $dateInterval
   * @param Collection<string> $typesOfLocation
   * @param int $maxLevels
   * @return Collection<GovReference>
   */
  public function govPgov(GovReference $gov, GedcomDateInterval $dateInterval, Collection $typesOfLocation, int $maxLevels = PHP_INT_MAX): Collection;
}
