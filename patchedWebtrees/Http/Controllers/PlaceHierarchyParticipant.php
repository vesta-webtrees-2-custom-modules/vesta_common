<?php

namespace Cissee\WebtreesExt\Http\Controllers;

use Fisharebest\Webtrees\Tree;

interface PlaceHierarchyParticipant {

  public function participates(Tree $tree): bool;
          
  public function filterLabel(): string;
  
  public function filterParameterName(): string;
  
  public function findPlace(
          int $id, 
          Tree $tree, 
          PlaceUrls $urls,
          bool $asAdditionalParticipant = false): PlaceWithinHierarchy;

}
