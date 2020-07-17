<?php

namespace Cissee\WebtreesExt\Http\Controllers;

use Cissee\WebtreesExt\Http\Controllers\PlaceUrls;
use Cissee\WebtreesExt\Http\Controllers\PlaceWithinHierarchy;
use Fisharebest\Webtrees\Gedcom;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Place;
use Fisharebest\Webtrees\PlaceLocation;
use Fisharebest\Webtrees\Services\SearchService;
use Fisharebest\Webtrees\Statistics;
use Fisharebest\Webtrees\Tree;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\Expression;
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
  
  //original impl in Place doesn't use the id, which is inefficient
  //(obtained later one-by-one via id())
  public function getChildPlacesCacheIds(Place $place): Collection
    {
        if ($place->gedcomName() !== '') {
            $parent_text = Gedcom::PLACE_SEPARATOR . $place->gedcomName();
        } else {
            $parent_text = '';
        }

        $tree = $place->tree();
        
        return DB::table('places')
            ->where('p_file', '=', $tree->id())
            ->where('p_parent_id', '=', $place->id())
            ->orderBy(new Expression('p_place /*! COLLATE ' . I18N::collation() . ' */'))
            ->pluck('p_place', 'p_id')
            ->map(function (string $place, int $id) use ($parent_text, $tree): Place {
                $place = new Place($place . $parent_text, $tree);
                app('cache.array')->remember('place-' . $place->gedcomName(), function () use ($id): int {return $id;});
                return $place;
            });
    }
    
  public function getChildPlaces(): array {
    $self = $this;    
    $ret = $this
            ->getChildPlacesCacheIds($this->actual)
            ->mapWithKeys(static function (Place $place) use ($self): array {
              return [$place->id() => new DefaultPlaceWithinHierarchy($place, $self->urls, $self->search_service, $self->statistics)];
            })
            ->toArray();
    
    return $ret;
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
