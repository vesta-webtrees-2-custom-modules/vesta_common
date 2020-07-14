<?php

namespace Cissee\WebtreesExt\Http\Controllers;

use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;
use Vesta\Model\PlaceStructure;

//abstraction of Place/PlaceLocation functionality
interface PlaceWithinHierarchy extends PlaceWithinHierarchyBase {
  
  ////////////////////////////////////////////////////////////////////////////////
  //Place
  
  public function id(): int;
  
  public function tree(): Tree;
  
  /**
   * Get the lower level places.
   *
   * @return array<PlaceWithinHierarchy>, keyed by id
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
  
  public function searchIndividualsInPlace(): Collection;
  
  //may be more efficient than searchIndividualsInPlace()->count();
  public function countIndividualsInPlace(): int;
  
  public function searchFamiliesInPlace(): Collection;
  
  //may be more efficient than searchFamiliesInPlace()->count();
  public function countFamiliesInPlace(): int;
  
  public function boundingRectangleWithChildren(array $children): array;
  
  public function placeStructure(): ?PlaceStructure;
  
  public function additionalLinksHtmlBeforeName(): string;
  
  /**
   * 
   * @return Collection<PlaceHierarchyLinks>
   */
  public function links(): Collection;
}
