<?php

namespace Cissee\WebtreesExt\Http\Controllers;

use Cissee\WebtreesExt\Http\Controllers\PlaceWithinHierarchyBase;

class DelegatingPlaceWithinHierarchyBase implements PlaceWithinHierarchyBase {
  
  /** @var PlaceWithinHierarchyBase */
  protected $delegate;
  
  public function __construct(
          PlaceWithinHierarchyBase $delegate) {
    
    $this->delegate = $delegate;
  }
  
  public function url(): string {
    return $this->delegate->url();
  }
  
  public function gedcomName(): string {
    return $this->delegate->gedcomName();
  }
    
  public function parent(): PlaceWithinHierarchyBase {
    return $this->delegate->parent();
  }
  
  public function placeName(): string {
    return $this->delegate->placeName();
  }
}
