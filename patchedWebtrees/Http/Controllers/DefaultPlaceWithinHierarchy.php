<?php

namespace Cissee\WebtreesExt\Http\Controllers;

use Cissee\WebtreesExt\Http\Controllers\PlaceWithinHierarchy;
use Cissee\WebtreesExt\Http\Controllers\PlaceUrls;
use Fisharebest\Webtrees\Place;
use Fisharebest\Webtrees\PlaceLocation;
use Fisharebest\Webtrees\Services\SearchService;
use Fisharebest\Webtrees\Statistics;
use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;
use Vesta\Model\PlaceStructure;

class DefaultPlaceWithinHierarchy extends DefaultPlaceWithinHierarchyBase implements PlaceWithinHierarchy {

  /** @var PlaceUrls */
  protected $urls;
  
  /** @var SearchService */
  protected $search_service;
 
  /** @var Statistics */
  protected $statistics;
    
  protected $latLonInitialized = false;
  
  public function __construct(
          Place $actual,
          PlaceUrls $urls,
          SearchService $search_service, 
          Statistics $statistics) {
    
    parent::__construct($actual, $urls);
    $this->urls = $urls;
    $this->search_service = $search_service;
    $this->statistics = $statistics;
  }
  
  public function getChildPlaces(): array {
    $self = $this;
    $ret = new Collection($this->actual->getChildPlaces());
    
    return $ret
            ->mapWithKeys(static function (Place $place) use ($self): array {
              return [$place->id() => new DefaultPlaceWithinHierarchy($place, $self->urls, $self->search_service, $self->statistics)];
            })
            ->toArray();
  }
  
  public function id(): int {
    return $this->actual->id();
  }
  
  public function tree(): Tree {
    return $this->actual->tree();
  }
  
  public function fullName(bool $link = false): string {
    return $this->actual->fullName($link);
  }
  
  public function searchIndividualsInPlace(): Collection {
    return $this->search_service->searchIndividualsInPlace($this->actual);    
  }
  
  public function countIndividualsInPlace(): int {
    //more efficient than searchIndividualsInPlace()->count();
    $tmp = $this->statistics->statsPlaces('INDI', '', $this->actual->id());
    return $tmp === [] ? 0 : $tmp[0]->tot;
  }
  
  public function searchFamiliesInPlace(): Collection {
    return $this->search_service->searchFamiliesInPlace($this->actual);    
  }
  
  public function countFamiliesInPlace(): int {
    //more efficient than searchFamiliesInPlace()->count();
    $tmp = $this->statistics->statsPlaces('FAM', '', $this->actual->id());
    return $tmp === [] ? 0 : $tmp[0]->tot;
  }
  
  public function latitude(): float {
    $pl = new PlaceLocation($this->gedcomName());
    return $pl->latitude();
  }
  
  public function longitude(): float {
    $pl = new PlaceLocation($this->gedcomName());
    return $pl->longitude();
  }
  
  public function icon(): string {
    $pl = new PlaceLocation($this->gedcomName());
    return $pl->icon();
  }
  
  public function boundingRectangleWithChildren(array $children_unused): array {
    //ugly bounding rectangle in case of only one coordinate!
    $pl = new PlaceLocation($this->gedcomName());
    return $pl->boundingRectangle();
  }
    
  public function placeStructure(): ?PlaceStructure {
    return PlaceStructure::fromPlace($this->actual);
  }

  public function additionalLinksHtmlBeforeName(): string {
    return '';
  }
  
  public function links(): Collection {
    return $this->urls->links($this->actual);
  }
}
