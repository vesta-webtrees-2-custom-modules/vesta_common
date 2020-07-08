<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Http\Controllers;

use Exception;
use Fisharebest\Webtrees\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Place;
use Fisharebest\Webtrees\Site;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Webtrees;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function array_chunk;
use function array_pop;
use function array_reverse;
use function assert;
use function ceil;
use function count;
use function is_file;
use function redirect;
use function view;

/**
 * Class GenericPlaceHierarchyController
 */
class GenericPlaceHierarchyController
{
    use ViewResponseTrait;
  
    /** @var PlaceHierarchyUtils */
    private $utils;

    /**
     * GenericPlaceHierarchyController constructor.
     *
     * @param PlaceHierarchyUtils $utils
     */
    public function __construct(PlaceHierarchyUtils $utils)
    {
        $this->utils = $utils;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function show(ServerRequestInterface $request): ResponseInterface
    {
        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);

        $module   = $request->getAttribute('module');
        $action   = $request->getAttribute('action');
        $action2  = $request->getQueryParams()['action2'] ?? 'hierarchy';
        $place_id = (int) ($request->getQueryParams()['place_id'] ?? 0);
        $place    = $this->utils->findPlace($place_id, $tree);

        // Request for a non-existent place?
        if ($place_id !== $place->id()) {
            return redirect($place->url());
        }

        $content    = '';
        $showmap    = Site::getPreference('map-provider') !== '';
        $data       = null;

        if ($showmap) {
            $content .= view('modules/place-hierarchy/map', [
                'data'     => $this->mapData($place),
                'provider' => [
                    'url'    => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                    'options' => [
                        'attribution' => '<a href="https://www.openstreetmap.org/copyright">&copy; OpenStreetMap</a> contributors',
                        'max_zoom'    => 19
                    ]
                ]
            ]);
        }

        switch ($action2) {
            case 'list':
                $nextaction = ['hierarchy' => $this->utils->hierarchyActionLabel()];
                $content .= view($this->utils->listView(), $this->getList($tree));
                break;
            case 'hierarchy':
            case 'hierarchy-e':
                $nextaction = ['list' => $this->utils->listActionLabel()];
                $data       = $this->getHierarchy($place);
                $content .= (null === $data || $showmap) ? '' : view($this->utils->placeHierarchyView(), $data);
                if (null === $data || $action2 === 'hierarchy-e') {
                    $content .= view('modules/place-hierarchy/events', [
                        'indilist' => $place->searchIndividualsInPlace(),
                        'famlist'  => $place->searchFamiliesInPlace(),
                        'tree'     => $place->tree(),
                    ]);
                }
                break;
            default:
                throw new HttpNotFoundException('Invalid action');
        }

        $breadcrumbs = $this->breadcrumbs($place);

        return $this->viewResponse(
            $this->utils->pageView(),
            [
                'utils'          => $this->utils,
                'title'          => $this->utils->pageLabel(),
                'tree'           => $tree,
                'current'        => $breadcrumbs['current'],
                'breadcrumbs'    => $breadcrumbs['breadcrumbs'],
                'place'          => $place,
                'content'        => $content,
                'showeventslink' => null !== $data && $place->gedcomName() !== '' && $action2 !== 'hierarchy-e',
                'nextaction'     => $nextaction,
                'module'         => $module,
                'action'         => $action,
            ]
        );
    }

    /**
     * @param Tree $tree
     *
     * @return Place[][]
     */
    private function getList(Tree $tree): array
    {
        $places = $this->utils->searchPlaces($tree)
            ->sort(static function (PlaceWithinHierarchy $x, PlaceWithinHierarchy $y): int {
                return $x->gedcomName() <=> $y->gedcomName();
            })
            ->all();

        $numfound = count($places);

        if ($numfound === 0) {
            $columns = [];
        } else {
            $divisor = $numfound > 20 ? 3 : 2;
            $columns = array_chunk($places, (int) ceil($numfound / $divisor));
        }

        return [
            'columns' => $columns,
        ];
    }


    /**
     * @param PlaceWithinHierarchy $place
     *
     * @return array{'tree':Tree,'col_class':string,'columns':array<array<PlaceWithinHierarchy>>,'place':PlaceWithinHierarchy}|null
     * @throws Exception
     */
    private function getHierarchy(PlaceWithinHierarchy $place): ?array
    {
        $child_places = $place->getChildPlaces();
        $numfound     = count($child_places);

        if ($numfound > 0) {
            $divisor = $numfound > 20 ? 3 : 2;

            return
                [
                    'tree'      => $place->tree(),
                    'col_class' => 'w-' . ($divisor === 2 ? '25' : '50'),
                    'columns'   => array_chunk($child_places, (int) ceil($numfound / $divisor)),
                    'place'     => $place,
                ];
        }

        return null;
    }

    /**
     * @param PlaceWithinHierarchy $place
     *
     * @return array{'breadcrumbs':array<PlaceWithinHierarchy>,'current':PlaceWithinHierarchy|null}
     */
    private function breadcrumbs(PlaceWithinHierarchy $place): array
    {
        $breadcrumbs = [];
        if ($place->gedcomName() !== '') {
            $breadcrumbs[] = $place;
            $parent_place  = $place->parent();
            while ($parent_place->gedcomName() !== '') {
                $breadcrumbs[] = $parent_place;
                $parent_place  = $parent_place->parent();
            }
            $breadcrumbs = array_reverse($breadcrumbs);
            $current     = array_pop($breadcrumbs);
        } else {
            $current = null;
        }

        return [
            'breadcrumbs' => $breadcrumbs,
            'current'     => $current,
        ];
    }

    /**
     * @param PlaceWithinHierarchy $placeObj
     *
     * @return array
     */
    protected function mapData(PlaceWithinHierarchy $placeObj): array
    {
        $places    = $placeObj->getChildPlaces();
        $features  = [];
        $sidebar   = '';
        $flag_path = Webtrees::MODULES_DIR . 'openstreetmap/';
        $show_link = true;

        if ($places === []) {
            $places[] = $placeObj;
            $show_link = false;
        }

        $locations = [];
        foreach ($places as $id => $place) {
            $location = $place;
            
            //[RC] added, may be more efficient to re-use
            $locations[] = $location;
            
            if ($location->icon() !== '' && is_file($flag_path . $location->icon())) {
                $flag = $flag_path . $location->icon();
            } else {
                $flag = '';
            }

            if ($location->latitude() === 0.0 && $location->longitude() === 0.0) {
                $sidebar_class = 'unmapped';
            } else {
                $sidebar_class = 'mapped';
                $features[]    = [
                    'type'       => 'Feature',
                    'id'         => $id,
                    'geometry'   => [
                        'type'        => 'Point',
                        'coordinates' => [$location->longitude(), $location->latitude()],
                    ],
                    'properties' => [
                        'tooltip' => $place->gedcomName(),
                        'popup'   => view('modules/place-hierarchy/popup', [
                            'showlink'  => $show_link,
                            'flag'      => $flag,
                            'place'     => $place,
                            'latitude'  => $location->latitude(),
                            'longitude' => $location->longitude(),
                        ]),
                    ],
                ];
            }

            //Stats
            $placeStats = [];
            $placeStats['INDI'] = $place->searchIndividualsInPlace()->count();
            $placeStats['FAM'] = $place->searchFamiliesInPlace()->count();
            
            $sidebar .= view($this->utils->sidebarView(), [
                'showlink'      => $show_link,
                'flag'          => $flag,
                'id'            => $id,
                'place'         => $place,
                'sidebar_class' => $sidebar_class,
                'stats'         => $placeStats,
            ]);
        }

        return [
            'bounds'  => $placeObj->boundingRectangleWithChildren($locations),
            'sidebar' => $sidebar,
            'markers' => [
                'type'     => 'FeatureCollection',
                'features' => $features,
            ]
        ];
    }
}
