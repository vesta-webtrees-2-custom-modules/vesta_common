<?php

namespace Cissee\WebtreesExt\Http\Controllers;

use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;

//abstraction of Place/PlaceLocation functionality
interface PlaceWithinHierarchy extends PlaceWithinHierarchyBase {
  
  ////////////////////////////////////////////////////////////////////////////////
  //Place
  
  public function id(): int;
  
  public function tree(): Tree;
  
  /**
   * Get the lower level places.
   *
   * @return array<PlaceWithinHierarchy>
   */
  public function getChildPlaces(): array;
  
  //for views
  public function fullName(bool $link = false): string;
  
  ////////////////////////////////////////////////////////////////////////////////
  //PlaceLocation
  
  public function icon(): string;
  
  public function latitude(): float;
  
  public function longitude(): float;
 
  ////////////////////////////////////////////////////////////////////////////////
  //extensions
  
  //SearchService::searchIndividualsInPlace
  public function searchIndividualsInPlace(): Collection;
  
  //SearchService::searchFamiliesInPlace
  public function searchFamiliesInPlace(): Collection;
  
  public function boundingRectangleWithChildren(array $children): array;
}
