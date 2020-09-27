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

class GenericPlaceHierarchyController {
    use ViewResponseTrait;
  
    /** @var PlaceHierarchyUtils */
    private $utils;

    private $detailsThreshold;
            
    /**
     * GenericPlaceHierarchyController constructor.
     *
     * @param PlaceHierarchyUtils $utils
     */
    public function __construct(PlaceHierarchyUtils $utils, int $detailsThreshold) {
        $this->utils = $utils;
        $this->detailsThreshold = $detailsThreshold;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function show(ServerRequestInterface $request): ResponseInterface {
        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);

        $module   = $request->getAttribute('module');
        $action   = $request->getAttribute('action');
        $action2  = $request->getQueryParams()['action2'] ?? 'hierarchy';
        $place_id = (int) ($request->getQueryParams()['place_id'] ?? 0);
        $place    = $this->utils->findPlace($place_id, $tree, $request->getQueryParams());
                
        // Request for a non-existent place?
        if ($place_id !== $place->id()) {
            return redirect($place->url());
        }

        $content    = '';
        $showmap    = Site::getPreference('map-provider') !== '';
        $data       = null;

        $places = null;
        if ($showmap) {
            //Speedup #4
            //no need to retrieve them more than once!
            $places = $place->getChildPlaces();

            $content .= view('modules/place-hierarchy/map', [
                'data'     => $this->mapData($place, $places),
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
                $content .= view($this->utils->listView(), $this->getList($tree, $request));
                break;
            case 'hierarchy':
            case 'hierarchy-e':
                $nextaction = ['list' => $this->utils->listActionLabel()];
                $data       = $this->getHierarchy($place, $places);
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

        $urlFilters = $this->utils->getUrlFilters($request->getQueryParams());
        
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
                'urlFilters'     => $urlFilters,
            ]
        );
    }

    /**
     * @param Tree $tree
     *
     * @return Place[][]
     */
    private function getList(Tree $tree, ServerRequestInterface $request): array {
        $topLevel = $this->utils->findPlace(0, $tree, $request->getQueryParams());
        $places = $topLevel->getChildPlaces();
            
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
    private function getHierarchy(PlaceWithinHierarchy $place, $places): ?array {
        $child_places = ($places !== null)?$places:$place->getChildPlaces();
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
    private function breadcrumbs(PlaceWithinHierarchy $place): array {
        $breadcrumbs = [];
        $breadcrumbs[] = $place;
        while ($place->gedcomName() !== '') {          
          $place = $place->parent();
          $breadcrumbs[] = $place;
        }
        $breadcrumbs = array_reverse($breadcrumbs);
        $current = array_pop($breadcrumbs);

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
    protected function mapData(PlaceWithinHierarchy $placeObj, $places): array {
        $features  = [];
        $sidebar   = '';
        $flag_path = Webtrees::MODULES_DIR . 'openstreetmap/';
        $show_link = true;

        if ($places === []) {
            $places[] = $placeObj;
            $show_link = false;
        }

        //Speedup #3
        //details only up to a given threshold 
        //(relevant for stats in general (i.e. should be in main webtrees),
        //but also for our more complex location function)
        $showDetails = sizeof($places) <= $this->detailsThreshold;
      
        $locations = [];
        foreach ($places as $id => $place) {
            /* @var $location PlaceWithinHierarchy */
            $location = $place;
            
            //[RC] added, may be more efficient to re-use
            $locations[] = $location;
            
            $sidebar_class = '';
            $flag = '';
            
            if ($showDetails) {
              if ($location->icon() !== '' && is_file($flag_path . $location->icon())) {
                  $flag = $flag_path . $location->icon();
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
            }

            //Stats
            $placeStats = [];
            if ($showDetails) {
              $placeStats['INDI'] = $place->countIndividualsInPlace();
              $placeStats['FAM'] = $place->countFamiliesInPlace();              
            }
            
            $sidebar .= view($this->utils->sidebarView(), [
                'showlink'      => $show_link,
                'flag'          => $flag,
                'id'            => $id,
                'place'         => $place,
                'sidebar_class' => $sidebar_class,
                'stats'         => $placeStats,
                
                'showDetails'   => $showDetails,
            ]);
        }

        $bounds = [[-180.0, -90.0], [180.0, 90.0]];
        if ($showDetails) {
          $bounds = $placeObj->boundingRectangleWithChildren($locations);
        }
        
        return [
            'bounds'  => $bounds,
            'sidebar' => $sidebar,
            'markers' => [
                'type'     => 'FeatureCollection',
                'features' => $features,
            ]
        ];
    }
}
