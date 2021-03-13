<?php

namespace Cissee\WebtreesExt\Http\Controllers;

use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;
use Vesta\Model\MapCoordinates;

//abstraction of Place/PlaceLocation functionality
interface PlaceWithinHierarchy {
  
  public function parent(): PlaceWithinHierarchy;  
  
  ////////////////////////////////////////////////////////////////////////////////
  //Place
  
  public function id(): int;
  
  public function tree(): Tree;
  
  public function url(): string;
  
  public function gedcomName(): string;
  
  /**
   * Get the lower level places.
   * 
   * @return array<PlaceWithinHierarchy>, keyed by id
   */
  public function getChildPlaces(): array;
  
  //for views
  public function placeName(): string;
  
  //for views
  public function fullName(bool $link = false): string;
  
  ////////////////////////////////////////////////////////////////////////////////
  //PlaceLocation
  
  public function icon(): string;
  
  public function getLatLon(): ?MapCoordinates;
  
  public function latitude(): ?float;
  
  public function longitude(): ?float;
 
  ////////////////////////////////////////////////////////////////////////////////
  //extensions
  
  public function searchIndividualsInPlace(): Collection;
  
  //may be more efficient than searchIndividualsInPlace()->count();
  public function countIndividualsInPlace(): int;
  
  public function searchFamiliesInPlace(): Collection;
  
  //may be more efficient than searchFamiliesInPlace()->count();
  public function countFamiliesInPlace(): int;
  
  public function boundingRectangleWithChildren(array $children): array;
  
  //Internal API!
  //public function placeStructure(): ?PlaceStructure;
  
  public function additionalLinksHtmlBeforeName(): string;
  
  /**
   * 
   * @return Collection<PlaceHierarchyLinks>
   */
  public function links(): Collection;
}
