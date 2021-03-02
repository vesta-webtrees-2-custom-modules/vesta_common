<?php

namespace Cissee\WebtreesExt\Http\Controllers;

use Fisharebest\Webtrees\Tree;


interface PlaceHierarchyUtils {
  
  public function getUrlFilters(array $requestParameters): array;
  
  //Place:::find
  //$requestParameters may or may not be evaluated
  public function findPlace(
          int $id, 
          Tree $tree, 
          array $requestParameters): PlaceWithinHierarchy;
    
  //I18N::translate('Show place hierarchy')
  public function hierarchyActionLabel(): string;
  
  //I18N::translate('Show all places in a list')
  public function listActionLabel(): string;
  
  //I18N::translate('Places')
  public function pageLabel(): string;
  
  //'place-hierarchy'
  public function placeHierarchyView(): string;
  
  //'modules/place-hierarchy/list'
  public function listView(): string;
  
  //'modules/place-hierarchy/page'
  public function pageView(): string;
  
  //'modules/place-hierarchy/sidebar'
  public function sidebarView(): string;
}
