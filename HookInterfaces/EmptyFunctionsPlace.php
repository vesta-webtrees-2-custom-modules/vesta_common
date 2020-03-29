<?php

namespace Vesta\Hook\HookInterfaces;

use Illuminate\Support\Collection;
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

  public function plac2Map(PlaceStructure $ps): ?MapCoordinates {
    return null;
  }
  
  public function plac2Loc(PlaceStructure $ps): ?LocReference {
    return null;
  }
  
  public function plac2Gov(PlaceStructure $ps): ?GovReference {
    return null;
  }

  public function govs2Placenames(Collection $govs): Collection {
    return new Collection();
  }
  
  public function loc2Map(LocReference $loc): ?MapCoordinates {
    return null;
  }
    
  public function loc2gov(LocReference $loc): ?GovReference {
    return null;
  }
  
  public function gov2map(GovReference $gov): ?MapCoordinates {
    return null;
  }

  public function hPlacesGetParentPlaces(PlaceStructure $place, $typesOfLocation, $recursively = false) {
    return array();
  }

}

?>