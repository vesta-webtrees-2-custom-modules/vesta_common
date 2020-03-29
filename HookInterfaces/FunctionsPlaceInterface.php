<?php

namespace Vesta\Hook\HookInterfaces;

use Fisharebest\Webtrees\Place;
use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;
use Vesta\Model\GovReference;
use Vesta\Model\LocReference;
use Vesta\Model\MapCoordinates;
use Vesta\Model\PlaceStructure;

/**
 * Hooks for additional functions on places
 */
interface FunctionsPlaceInterface {

  public function setPlacesOrder(int $order): void;

  public function getPlacesOrder(): int;

  public function defaultPlacesOrder(): int;


  /**
   * impls must not traverse the place hierarchy (given or implied) in any way,
   * nor attempt to aggregate via other plac2x, x2map functions themselves.
   * 
   * @param PlaceStructure $ps
   * @return MapCoordinates|null
   */
  public function plac2Map(PlaceStructure $ps): ?MapCoordinates;
  
  /**
   * impls must not traverse the place hierarchy (given or implied) in any way.
   * 
   * @param PlaceStructure $ps
   * @return LocReference|null
   */
  public function plac2Loc(PlaceStructure $ps): ?LocReference;

  /**
   * impls must not traverse the place hierarchy (given or implied) in any way,
   * nor attempt to aggregate via other plac2x, x2gov functions themselves.
   * 
   * @param PlaceStructure $ps
   * @return GovReference|null
   */
  public function plac2Gov(PlaceStructure $ps): ?GovReference;

  /**
   * batched for better performance 
   * (note that even a single GovReference may map to multiple placenames!)
   * 
   * impls must not traverse or aggregate!
   * 
   * @param Collection<GovReference> $gov
   * @return Collection<string>
   */
  public function govs2Placenames(Collection $gov): Collection;
  
  /**
   * impls must not traverse the place hierarchy (given or implied) in any way,
   * nor attempt to aggregate via other loc2x, x2map functions themselves.
   * 
   * @param LocReference $loc
   * @return MapCoordinates|null
   */
  public function loc2Map(LocReference $loc): ?MapCoordinates;
  
  /**
   * impls must not traverse the place hierarchy (given or implied) in any way.
   * 
   * @param LocReference $loc
   * @return GovReference|null
   */
  public function loc2gov(LocReference $loc): ?GovReference;
  
  /**
   * impls must not traverse the place hierarchy (given or implied) in any way.
   * 
   * @param GovReference $gov
   * @return MapCoordinates|null
   */
  public function gov2map(GovReference $gov): ?MapCoordinates;

  /**
   *
   * @param $place
   * @param array|null $typesOfLocation if non-null, restrict to places of given type (POLI|RELI|GEOG|CULT)
   * @param boolean $recursively
   *
   * @return array parent places (use event date, if possible, to filter and return parent places relevant at given date)
   * 		 		 
   */
  public function hPlacesGetParentPlaces(PlaceStructure $place, $typesOfLocation, $recursively = false);
}
