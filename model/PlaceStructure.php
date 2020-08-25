<?php

namespace Vesta\Model;

use Closure;
use Fisharebest\Webtrees\Fact;
use Fisharebest\Webtrees\Functions\Functions;
use Fisharebest\Webtrees\Location;
use Fisharebest\Webtrees\Place;
use Fisharebest\Webtrees\Tree;
use Vesta\Model\GedcomDateInterval;

/**
 * A GEDCOM level 2 place (PLAC) object (complete structure, may include custom tags)
 * plus event type and date
 *   
 */
class PlaceStructure {

  private $tree;
  private $gedcomName;
  private $gedcom;
  private $eventType;
  private $eventDateInterval;
  private $level;
  private $location;
  
  // Regular expression to match a GEDCOM XREF.
  //cf WT_REGEX_XREF (1.x)/ Gedcom::REGEX_XREF (2.x)
  const REGEX_XREF = '[A-Za-z0-9:_-]+';

  public function getTree(): Tree {
    return $this->tree;
  }

  public function getGedcomName(): string {
    return $this->gedcomName;
  }
  
  //discouraged, preferably use other getters! 
  public function getGedcom(): string {
    return $this->gedcom;
  }
  
  /**
   * @return string|null tag of the level 1 event, if any
   */
  public function getEventType(): ?string {
    return $this->eventType;
  }

  /**
   * @return GedcomDateInterval date interval of the level 1 event
   */
  public function getEventDateInterval(): GedcomDateInterval {
    return $this->eventDateInterval;
  }

  public function getLevel(): int {
    return $this->level;
  }
  
  private function __construct(
          string $gedcomName, 
          string $gedcom, 
          Tree $tree, 
          ?string $eventType, 
          GedcomDateInterval $eventDateInterval, 
          int $level = 0,
          ?Location $location = null) {
    
    $this->gedcomName = $gedcomName;
    $this->gedcom = $gedcom;
    $this->tree = $tree;
    $this->eventType = $eventType;
    $this->eventDateInterval = $eventDateInterval;
    $this->level = $level;
    $this->location = $location;
  }

  public static function create(
          string $gedcom, 
          Tree $tree, 
          ?string $eventType = null, 
          ?string $eventDateGedcomString = null, 
          int $level = 0,
          ?Location $location = null): ?PlaceStructure {
    
    $gedcomName = '';

    if (preg_match('/^2 PLAC (.+)/', $gedcom, $match)) {
      $gedcomName = $match[1];
    }
    if ($gedcomName === '') {
      //no - this isn't valid GEDCOM!
      //$placerec = "2 PLAC \n";
      //error_log("Invalid place: ". $gedcom);
      //$ex = new \Exception();
      //error_log(print_r($ex->getTraceAsString(), true));
      return null;
    }
    
    $dateInterval = GedcomDateInterval::createEmpty();
    if ($eventDateGedcomString !== null) {
      $dateInterval = GedcomDateInterval::create($eventDateGedcomString);
    }
    return new PlaceStructure($gedcomName, $gedcom, $tree, $eventType, $dateInterval, $level, $location);
  }
  
  public static function fromName(string $name, Tree $tree): ?PlaceStructure {
    $gedcom = "2 PLAC " . $name;
    return PlaceStructure::create($gedcom, $tree);
  }
  
  public static function fromNameAndGov(string $name, string $gov, Tree $tree, int $level = 0): ?PlaceStructure {
    $gedcom = "2 PLAC " . $name . "\n3 _GOV @" . $gov . "@";
    return PlaceStructure::create($gedcom, $tree, null, null, $level);
  }
  
  public static function fromNameAndLoc(string $name, string $loc, Tree $tree, int $level = 0, ?Location $location = null): ?PlaceStructure {
    $gedcom = "2 PLAC " . $name . "\n3 _LOC @" . $loc . "@";
    return PlaceStructure::create($gedcom, $tree, null, null, $level, $location);
  }
  
  public static function fromNameAndLocNow(string $name, string $loc, Tree $tree, int $level = 0, ?Location $location = null): ?PlaceStructure {
    $gedcom = "2 PLAC " . $name . "\n3 _LOC @" . $loc . "@";
    $dateInterval = GedcomDateInterval::createNow();
    return PlaceStructure::create($gedcom, $tree, null, $dateInterval->toGedcomString(2), $level, $location);
  }
  
  public static function fromFact(Fact $event): ?PlaceStructure {
    $placerec = Functions::getSubRecord(2, '2 PLAC', $event->gedcom());

    $ps = PlaceStructure::create(
            $placerec, 
            $event->record()->tree(), 
            $event->getTag(), 
            $event->attribute("DATE"));
    return $ps;
  }
  
  public static function fromFactWithExplicitInterval(Fact $event, GedcomDateInterval $dateInterval): ?PlaceStructure {
    $placerec = Functions::getSubRecord(2, '2 PLAC', $event->gedcom());
    $ps = PlaceStructure::create(
            $placerec, 
            $event->record()->tree(), 
            $event->getTag(), 
            $dateInterval->toGedcomString(2));
    
    return $ps;
  }
  
  public static function fromPlace(Place $place): ?PlaceStructure {
    $gedcom = "2 PLAC " . $place->gedcomName();
    return PlaceStructure::create($gedcom, $place->tree());
  }

  /**
   *
   * @return Place	 
   */
  public function getPlace() {
    return new Place($this->getGedcomName(), $this->tree);
  }

  public function getLocation(): ?Location {
    return $this->location;
  }
  
  /**
   * helper for those who are aware of this custom tag
   * 
   * @return string|null
   */
  public function getLoc() {
    if (preg_match('/3 _LOC @(' . PlaceStructure::REGEX_XREF . ')@/', $this->getGedcom(), $match)) {
      return $match[1];
    }

    return null;
  }

  /**
   * helper for those who are aware of this custom tag
   * 
   * @return string|null
   */
  public function getGov() {
    if (preg_match('/3 _GOV (.*)/', $this->getGedcom(), $match)) {
      return $match[1];
    }

    return null;
  }
  
  public function getLati() {
    //cf FunctionsPrint
    $map_lati = null;
    $cts = preg_match('/4 LATI (.*)/', $this->getGedcom(), $match);
    if ($cts > 0) {
      $map_lati = $match[1];
    }
    if ($map_lati) {
      $map_lati = trim(strtr($map_lati, "NSEW,�", " - -. ")); // S5,6789 ==> -5.6789
      return $map_lati;
    }
    return null;
  }

  public function getLong() {
    //cf FunctionsPrint
    $map_long = null;
    $cts = preg_match('/4 LONG (.*)/', $this->getGedcom(), $match);
    if ($cts > 0) {
      $map_long = $match[1];
    }
    if ($map_long) {
      $map_long = trim(strtr($map_long, "NSEW,�", " - -. ")); // E3.456� ==> 3.456
      return $map_long;
    }
    return null;
  }

  public function parent(): ?PlaceStructure {
    //error_log("parentGedcom? ". $this->getGedcom());
    
    //important to include linebreaks in '.*' via '/s'!
    if (preg_match('/^2 PLAC [^,]+, (.+)/s', $this->getGedcom(), $match)) {
      $parentGedcom = "2 PLAC " . $match[1];
      
      //error_log("parentGedcom ". $parentGedcom);
      
      return PlaceStructure::create(
            $parentGedcom,
            $this->getTree(), 
            $this->getEventType(), 
            $this->getEventDateInterval()->toGedcomString(2),
            $this->getLevel() + 1);
    }
    
    return null;
  }
  
  public function debug(): string {
    return 
      $this->tree->name() . ' :: ' . 
      $this->gedcomName  . ' :: ' . 
      $this->gedcom  . ' :: ' . 
      $this->eventType  . ' :: ' . 
      $this->eventDateInterval->toGedcomString(0)  . ' :: ' . 
      $this->level;
  }
  
  public static function sorterByLevel(): Closure {
    return function (PlaceStructure $x, PlaceStructure $y): int {
      return $x->getLevel() <=> $y->getLevel();
    };
  }
}
