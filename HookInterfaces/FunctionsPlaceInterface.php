<?php

namespace Vesta\Hook\HookInterfaces;

use Vesta\Model\PlaceStructure;

/**
 * Hooks for additional functions on places
 */
interface FunctionsPlaceInterface {

  public function setPlacesOrder(int $order): void;

  public function getPlacesOrder(): int;

  public function defaultPlacesOrder(): int;

  /**
   *
   * @param $place
   *
   * @return array|null (array of integer) Latitude/Longitude of a fact, obtained from somewhere outside the fact's own gedcom
   * impls must not traverse the place hierarchy in any way!
   * 
   * 		 		 
   */
  public function hPlacesGetLatLon(PlaceStructure $place);

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
