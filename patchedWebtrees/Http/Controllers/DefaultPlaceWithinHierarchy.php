<?php

namespace Cissee\WebtreesExt\Http\Controllers;

use Cissee\WebtreesExt\Http\Controllers\PlaceUrls;
use Cissee\WebtreesExt\Http\Controllers\PlaceWithinHierarchy;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Gedcom;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Place;
use Fisharebest\Webtrees\PlaceLocation;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Services\SearchService;
use Fisharebest\Webtrees\Tree;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use stdClass;
use Vesta\Model\MapCoordinates;
use Vesta\Model\PlaceStructure;

class DefaultPlaceWithinHierarchy implements PlaceWithinHierarchy {

    /** @var Place */
    protected $actual;

    /** @var PlaceUrls */
    protected $urls;

    /** @var SearchService */
    protected $search_service;

    protected $latLonInitialized = false;

    public function __construct(
        Place $actual,
        PlaceUrls $urls,
        SearchService $search_service) {

        $this->actual = $actual;
        $this->urls = $urls;
        $this->search_service = $search_service;
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

    public function parent(): PlaceWithinHierarchy {
        return new DefaultPlaceWithinHierarchy($this->actual->parent(), $this->urls, $this->search_service);
    }

    public function placeName(): string {
        return $this->actual->placeName();
    }

    //original impl in Place doesn't use the id, which is inefficient
    //(obtained later one-by-one via id())
    public function getChildPlacesCacheIds(Place $place): Collection {
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
                    Registry::cache()->array()->remember('place-' . $place->gedcomName(), function () use ($id): int {
                        return $id;
                    });
                    return $place;
                });
    }

    public function getChildPlaces(): array {
        $self = $this;
        $ret = $this
            ->getChildPlacesCacheIds($this->actual)
            ->mapWithKeys(static function (Place $place) use ($self): array {
                return [$place->id() => new DefaultPlaceWithinHierarchy($place, $self->urls, $self->search_service)];
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
        $tmp = $this->statsPlaces('INDI', $this->actual->id());
        return $tmp === [] ? 0 : $tmp[0]->tot;
    }

    public function searchFamiliesInPlace(): Collection {
        return $this->search_service->searchFamiliesInPlace($this->actual);
    }

    public function countFamiliesInPlace(): int {
        //more efficient than searchFamiliesInPlace()->count();
        $tmp = $this->statsPlaces('FAM', $this->actual->id());
        return $tmp === [] ? 0 : $tmp[0]->tot;
    }

    public function getLatLon(): ?MapCoordinates {
        //TODO return proper coordinates!
        return null;
    }

    public function latitude(): ?float {
        $pl = new PlaceLocation($this->gedcomName());
        return $pl->latitude();
    }

    public function longitude(): ?float {
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
    
    //adapted from PlaceRepository (2.0), method no longer exists in 2.1
    //was used in original 
    /**
     * Query places.
     *
     * @param string $what
     * @param string $fact
     * @param int    $parent
     * @param bool   $country
     *
     * @return int[]|stdClass[]
     */
    public function statsPlaces(string $what = 'ALL', int $parent = 0): array
    {
        $query = DB::table('places')
            ->join('placelinks', static function (JoinClause $join): void {
                $join->on('pl_file', '=', 'p_file')
                    ->on('pl_p_id', '=', 'p_id');
            })
            ->where('p_file', '=', $this->actual->tree()->id());

        if ($parent > 0) {
            // Used by placehierarchy map modules
            $query->select(['p_place AS place'])
                ->selectRaw('COUNT(*) AS tot')
                ->where('p_id', '=', $parent)
                ->groupBy(['place']);
        } else {
            $query->select(['p_place AS country'])
                ->selectRaw('COUNT(*) AS tot')
                ->where('p_parent_id', '=', 0)
                ->groupBy(['country'])
                ->orderByDesc('tot')
                ->orderBy('country');
        }

        if ($what === Individual::RECORD_TYPE) {
            $query->join('individuals', static function (JoinClause $join): void {
                $join->on('pl_file', '=', 'i_file')
                    ->on('pl_gid', '=', 'i_id');
            });
        } elseif ($what === Family::RECORD_TYPE) {
            $query->join('families', static function (JoinClause $join): void {
                $join->on('pl_file', '=', 'f_file')
                    ->on('pl_gid', '=', 'f_id');
            });
        } elseif ($what === Location::RECORD_TYPE) {
            $query->join('other', static function (JoinClause $join): void {
                $join->on('pl_file', '=', 'o_file')
                    ->on('pl_gid', '=', 'o_id');
            })
                ->where('o_type', '=', Location::RECORD_TYPE);
        }

        return $query
            ->get()
            ->map(static function (stdClass $entry) {
                // Map total value to integer
                $entry->tot = (int) $entry->tot;

                return $entry;
            })
            ->all();
    }
}
