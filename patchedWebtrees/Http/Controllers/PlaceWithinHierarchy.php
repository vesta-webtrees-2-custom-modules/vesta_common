<?php

namespace Cissee\WebtreesExt\Http\Controllers;

use Fisharebest\Webtrees\Place;
use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;

//abstraction of Place/PlaceLocation functionality
interface PlaceWithinHierarchy {
  
  ////////////////////////////////////////////////////////////////////////////////
  //Place
  
  public function id(): int;
  
  public function url(): string;
  
  public function tree(): Tree;
  
  public function gedcomName(): string;
    
  //ok to use place here - only evaluated wrt gedcomName() and parent()
  public function parent(): Place;  
  
  /**
   * Get the lower level places.
   *
   * @return array<PlaceWithinHierarchy>
   */
  public function getChildPlaces(): array;
    
  //for views
  public function placeName(): string;
  
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
