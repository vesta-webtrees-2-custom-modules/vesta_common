<?php

namespace Cissee\WebtreesExt\Http\Controllers;

use Cissee\WebtreesExt\Http\Controllers\PlaceWithinHierarchyBase;
use Fisharebest\Webtrees\Place;

class DefaultPlaceWithinHierarchyBase implements PlaceWithinHierarchyBase {
  
  /** @var Place */
  protected $actual;
  
  /** @var PlaceUrls */
  protected $urls;
  
  public function __construct(
          Place $actual,
          PlaceUrls $urls) {
    
    $this->actual = $actual;
    $this->urls = $urls;
  }
  
  //Speedup
  //more efficient than $this->actual->url() when calling for lots of places
  //this should be in webtrees, e.g. via caching result of 'findByComponent' or even 'Auth::accessLevel'
  public function url(): string {
    return $this->urls->url($this->actual);
  }
  
  public function gedcomName(): string {
    return $this->actual->gedcomName();
  }
    
  public function parent(): PlaceWithinHierarchyBase {
    return new DefaultPlaceWithinHierarchyBase($this->actual->parent(), $this->urls);
  }
  
  public function placeName(): string {
    return $this->actual->placeName();
  }
}
