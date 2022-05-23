<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt;

use Fisharebest\Webtrees\GedcomRecord;
use Fisharebest\Webtrees\Place;
use Fisharebest\Webtrees\Tree;
use Vesta\Model\PlaceStructure;

class PlaceAsTopLevelRecord extends GedcomRecord {
    
    protected Place $place;
    
    public function __construct(
        string $place_name, 
        Tree $tree) {
        
        parent::__construct("", "0 @@ _LOC\n1 NAME " . $place_name, null, $tree);
        $this->place = new Place($place_name, $tree);
    }
    
    public function url(): string {        
        return $this->place->url();
    }
    
    public function placeStructure(): ?PlaceStructure {
        return PlaceStructure::fromPlace($this->place);
    }
        
}
