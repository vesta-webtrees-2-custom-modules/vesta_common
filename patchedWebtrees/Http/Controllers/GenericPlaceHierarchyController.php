<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Http\Controllers;

use Cissee\Webtrees\Module\PPM\PlaceHierarchyUtilsImpl;
use Exception;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Http\RequestHandlers\MapDataEdit;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Module\ModuleListInterface;
use Fisharebest\Webtrees\Module\ModuleMapProviderInterface;
use Fisharebest\Webtrees\Place;
use Fisharebest\Webtrees\Services\LeafletJsService;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Services\SearchService;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Webtrees;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function app;
use function array_chunk;
use function array_pop;
use function array_reverse;
use function ceil;
use function count;
use function is_file;
use function redirect;
use function route;
use function view;

//generalizes PlaceHierarchyListModule
class GenericPlaceHierarchyController implements RequestHandlerInterface {
    use ViewResponseTrait;
  
    private ModuleListInterface $module;
    
    public function __construct(
        ModuleListInterface $module) {
        
        $this->module = $module;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $tree = Validator::attributes($request)->tree();
        $user = Validator::attributes($request)->user();

        Auth::checkComponentAccess($this->module, ModuleListInterface::class, $tree, $user);

        $searchService = app(SearchService::class);
        $participants = app(ModuleService::class)
            ->findByComponent(PlaceHierarchyParticipant::class, $tree, Auth::user())
            ->filter(function (PlaceHierarchyParticipant $php) use ($tree): bool {
            return $php->participates($tree);
        });

        $utils = new PlaceHierarchyUtilsImpl(
                $this->module,
                $participants,
                $searchService);
    
        $detailsThreshold = intval($this->module->getPreference('DETAILS_THRESHOLD', '100'));
                
        $action2  = $request->getQueryParams()['action2'] ?? 'hierarchy';
        $place_id = (int) ($request->getQueryParams()['place_id'] ?? 0);
        $place    = $utils->findPlace($place_id, $tree, $request->getQueryParams());
                
        // Request for a non-existent place?
        if ($place_id !== $place->id()) {
            return redirect($place->url());
        }

        $map_providers = app(ModuleService::class)->findByInterface(ModuleMapProviderInterface::class);

        $content = '';
        $showmap = $map_providers->isNotEmpty();
        $data    = null;

        $places = null;
        if ($showmap) {
            //Speedup #4
            //no need to retrieve them more than once!
            $places = $place->getChildPlaces();

            $content .= view('modules/place-hierarchy/map', [
                'data'           => $this->mapData($utils, $detailsThreshold, $place, $places),
                'leaflet_config' => app(LeafletJsService::class)->config(),
            ]);
        }

        $urlFilters = $utils->getUrlFilters($request->getQueryParams());

        switch ($action2) {
            case 'list':
                $alt_link = $utils->hierarchyActionLabel();
                $alt_url  = $this->module->listUrl($tree, ['action2' => 'hierarchy', 'place_id' => $place_id] + $urlFilters);
                
                $content .= view($utils->listView(), $this->getList($utils, $tree, $request));
                break;
            case 'hierarchy':
            case 'hierarchy-e':
                $alt_link = $utils->listActionLabel();
                $alt_url  = $this->module->listUrl($tree, ['action2' => 'list', 'place_id' => 0] + $urlFilters);
                
                $data       = $this->getHierarchy($place, $places);
                $content .= (null === $data || $showmap) ? '' : view($utils->placeHierarchyView(), $data);
                if (null === $data || $action2 === 'hierarchy-e') {
                    $content .= view($utils->eventsView(), [
                        'indilist'  => $place->searchIndividualsInPlace(),
                        'famlist'   => $place->searchFamiliesInPlace(),
                        'tree'      => $place->tree(),
                        'placename' => $place->gedcomName(),
                        'module'    => $this->module,
                    ]);
                }
                break;
            default:
                throw new HttpNotFoundException('Invalid action');
        }

        if ($data !== null && $action2 !== 'hierarchy-e' && $place->gedcomName() !== '') {
            $events_link = $this->module->listUrl($tree, ['action2' => 'hierarchy-e', 'place_id' => $place_id] + $urlFilters);
        } else {
            $events_link = '';
        }
        
        $breadcrumbs = $this->breadcrumbs($place);
        
        return $this->viewResponse(
            $utils->pageView(),
            [
                'utils'       => $utils,
                'urlFilters'  => $urlFilters,
                
                'alt_link'    => $alt_link,
                'alt_url'     => $alt_url,
                'breadcrumbs' => $breadcrumbs['breadcrumbs'],
                'content'     => $content,
                'current'     => $breadcrumbs['current'],
                'events_link' => $events_link,
                'place'       => $place,
                'title'       => $utils->pageLabel(),
                'tree'        => $tree,
            ]
        );
    }
    
    /**
     * @param Tree $tree
     *
     * @return Place[][]
     */
    private function getList(
        PlaceHierarchyUtils $utils,
        Tree $tree, 
        ServerRequestInterface $request): array {
        
        $topLevel = $utils->findPlace(0, $tree, $request->getQueryParams());
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
    protected function mapData(
        PlaceHierarchyUtils $utils,
        $detailsThreshold,
        PlaceWithinHierarchy $placeObj, 
        $places): array {
        
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
        $showDetails = sizeof($places) <= $detailsThreshold;
      
        $locations = [];
        foreach ($places as $id => $place) {
            
            //$id is just the array index (not the place id)
            if ($place->id() === 0) {
                continue; //skip empty top-level (empty e.g. due to filters)
            }
            
            /* @var $location PlaceWithinHierarchy */
            $location = $place;
            
            //[RC] added, may be more efficient to re-use
            $locations[] = $location;
            
            $sidebar_class = '';
            
            if (Auth::isAdmin()) {
                $this_url = route(self::class, ['tree' => $place->tree()->name(), 'place_id' => $place->id()]);
                $edit_url = route(MapDataEdit::class, ['location_id' => $location->id(), 'url' => $this_url]);
            } else {
                $edit_url = '';
            }
                
            if ($showDetails) {
                if ($location->latitude() === null && $location->longitude() === null) {
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
                              'edit_url'  => $edit_url,
                              'place'     => $place,
                              'latitude'  => $location->latitude(),
                              'longitude' => $location->longitude(),
                              'showlink'  => $show_link,
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
            
            $sidebar .= view($utils->sidebarView(), [
                'edit_url'      => $edit_url,
                'id'            => $id,
                'place'         => $place,
                'showlink'      => $show_link,
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
