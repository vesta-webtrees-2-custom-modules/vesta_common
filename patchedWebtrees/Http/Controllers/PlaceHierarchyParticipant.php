<?php

namespace Cissee\WebtreesExt\Http\Controllers;

use Fisharebest\Webtrees\Tree;

interface PlaceHierarchyParticipant {

  public function filterLabel(): string;
  
  public function filterParameterName(): string;
  
  public function findPlace(int $id, Tree $tree, PlaceUrls $urlS): PlaceWithinHierarchy;

}
