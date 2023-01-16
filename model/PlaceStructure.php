<?php

namespace Vesta\Model;

use Cissee\WebtreesExt\MoreI18N;
use Cissee\WebtreesExt\PlaceAsTopLevelRecord;
use Closure;
use Fisharebest\Webtrees\Fact;
use Fisharebest\Webtrees\Location;
use Fisharebest\Webtrees\Place;
use Fisharebest\Webtrees\Report\ReportParserGenerate;
use Fisharebest\Webtrees\Services\GedcomService;
use Fisharebest\Webtrees\Services\TreeService;
use Fisharebest\Webtrees\Tree;
use JsonSerializable;
use Vesta\Model\GedcomDateInterval;
use function app;
use function GuzzleHttp\json_encode;

/**
 * A GEDCOM level 2 place (PLAC) object (complete structure, may include custom tags)
 * plus event type and date
 *   
 */
class PlaceStructure implements JsonSerializable {

    private $tree;
    private $gedcomName;
    private $gedcom;
    private $eventType;
    private $eventDateInterval;
    private $level;
    private $location;
    private $virtual;

    #[\ReturnTypeWillChange]
    public function jsonSerialize() {
        return [
            'tree' => $this->tree->id(),
            'gedcomName' => $this->gedcomName,
            'gedcom' => $this->gedcom,
            'eventType' => $this->eventType,
            'eventDateInterval' => $this->eventDateInterval,
            'level' => $this->level,
            'location' => ($this->location !== null) ? $this->location->xref() : null,
            'virtual' => $this->virtual,
        ];
    }

    public function debug(): string {
        return json_encode($this);
    }

    // Regular expression to match a GEDCOM XREF.
    //cf WT_REGEX_XREF (1.x)/ Gedcom::REGEX_XREF (2.x)
    const REGEX_XREF = '[A-Za-z0-9:_-]+';

    //TODO use tree() instead!
    public function getTree(): Tree {
        return $this->tree;
    }

    public function tree(): Tree {
        return $this->tree;
    }

    public function getGedcomName(): string {
        return $this->gedcomName;
    }

    //TODO use gedcom() instead!
    public function getGedcom(): string {
        return $this->gedcom;
    }

    public function gedcom(): string {
        return $this->gedcom;
    }

    /**
     * @return string|null tag of the level 1 event, if any
     */
    public function getEventType(): ?string {
        return $this->eventType;
    }

    public function tag(): string {
        $tag = $this->eventType;
        if ($tag === null) {
            return '_UNKNOWN_'; //TODO better fallback?
        }
        return $tag;
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

    public function getLocation(): ?Location {
        return $this->location;
    }

    public function isVirtual(): bool {
        return $this->virtual;
    }

    private function __construct(
        string $gedcomName,
        string $gedcom,
        Tree $tree,
        ?string $eventType,
        bool $virtual,
        GedcomDateInterval $eventDateInterval,
        int $level = 0,
        ?Location $location = null) {

        $this->gedcomName = $gedcomName;
        $this->gedcom = $gedcom;
        $this->tree = $tree;
        $this->eventType = $eventType;
        $this->virtual = $virtual;
        $this->eventDateInterval = $eventDateInterval;
        $this->level = $level;
        $this->location = $location;
    }

    public static function fromStd($std): PlaceStructure {
        return new PlaceStructure(
            $std->gedcomName,
            $std->gedcom,
            app(TreeService::class)->find($std->tree),
            $std->eventType,
            $std->virtual,
            GedcomDateInterval::fromStd($std->eventDateInterval),
            $std->level,
            null); //hmmm
    }

    public static function create(
        string $gedcom,
        Tree $tree,
        ?string $eventType = null,
        bool $virtual = false,
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

        return new PlaceStructure(
            $gedcomName,
            $gedcom,
            $tree,
            $eventType,
            $virtual,
            $dateInterval,
            $level,
            $location);
    }

    public static function fromName(
        string $name,
        Tree $tree): ?PlaceStructure {

        $gedcom = "2 PLAC " . $name;
        return PlaceStructure::create($gedcom, $tree);
    }

    public static function fromNameAndGov(
        string $name,
        string $gov,
        Tree $tree,
        int $level = 0): ?PlaceStructure {

        $gedcom = "2 PLAC " . $name . "\n3 _GOV @" . $gov . "@";
        return PlaceStructure::create($gedcom, $tree, null, false, null, $level);
    }

    public static function fromNameAndLoc(
        string $name,
        string $loc,
        Tree $tree,
        int $level = 0,
        ?Location $location = null): ?PlaceStructure {

        $gedcom = "2 PLAC " . $name . "\n3 _LOC @" . $loc . "@";
        return PlaceStructure::create($gedcom, $tree, null, false, null, $level, $location);
    }

    public static function fromNameAndLocNow(
        string $name,
        string $loc,
        Tree $tree,
        int $level = 0,
        ?Location $location = null): ?PlaceStructure {

        $gedcom = "2 PLAC " . $name . "\n3 _LOC @" . $loc . "@";
        $dateInterval = GedcomDateInterval::createNow();
        return PlaceStructure::create($gedcom, $tree, null, false, $dateInterval->toGedcomString(2), $level, $location);
    }

    public static function fromNameAndLocWithYear(
        int $year,
        string $name,
        string $loc,
        Tree $tree,
        int $level = 0,
        ?Location $location = null): ?PlaceStructure {

        $gedcom = "2 PLAC " . $name . "\n3 _LOC @" . $loc . "@";
        $dateInterval = GedcomDateInterval::createYear($year);
        return PlaceStructure::create($gedcom, $tree, null, false, $dateInterval->toGedcomString(2), $level, $location);
    }

    public static function fromFact(
        Fact $event): ?PlaceStructure {

        $tag = explode(':', $event->tag())[1];

        //cannot use this - skips levels 3 tags
        //$placerec = '2 PLAC ' . $event->attribute('PLAC');
        $placerec = ReportParserGenerate::getSubRecord(2, '2 PLAC', $event->gedcom());

        $ps = PlaceStructure::create(
                $placerec,
                $event->record()->tree(),
                $tag,
                ($event->id() === 'histo'),
                $event->attribute("DATE"));

        return $ps;
    }

    public static function fromFactWithExplicitInterval(
        Fact $event,
        GedcomDateInterval $dateInterval): ?PlaceStructure {

        $tag = explode(':', $event->tag())[1];

        //cannot use this - skips levels 3 tags
        //$placerec = '2 PLAC ' . $event->attribute('PLAC');
        $placerec = ReportParserGenerate::getSubRecord(2, '2 PLAC', $event->gedcom());

        $ps = PlaceStructure::create(
                $placerec,
                $event->record()->tree(),
                $tag,
                ($event->id() === 'histo'),
                $dateInterval->toGedcomString(2));
        
        return $ps;
    }

    public static function fromPlace(
        Place $place): ?PlaceStructure {

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

    public function getLati(): ?string {
        
        //cf Fact->latitude()
        if (preg_match('/\n4 LATI (.+)/', $this->getGedcom(), $match)) {
            $gedcom_service = new GedcomService();

            $map_lati = $gedcom_service->readLatitude($match[1]);
            if ($map_lati !== null) {
                return '' . $map_lati;
            }
        }

        return null;
    }

    public function getLong(): ?string {
        
        //cf Fact->latitude()
        if (preg_match('/\n4 LONG (.+)/', $this->getGedcom(), $match)) {
            $gedcom_service = new GedcomService();

            $map_long = $gedcom_service->readLongitude($match[1]);
            if ($map_long !== null) {
                return '' . $map_long;
            }
        }

        return null;
    }

    public function parent(): ?PlaceStructure {

        //[2021/08] not sure what the idea was here: any level 3/4 tags aren't valid for the parent!
        //important to include linebreaks in '.*' via '/s'!
        //if (preg_match('/^2 PLAC [^,]+, (.+)/s', $this->getGedcom(), $match)) {
        //  $parentGedcom = "2 PLAC " . $match[1];

        $parentName = $this->getPlace()->parent()->gedcomName();
        if ($parentName !== '') {
            $parentGedcom = "2 PLAC " . $parentName;
            //error_log("parentGedcom ". $parentGedcom);

            return PlaceStructure::create(
                    $parentGedcom,
                    $this->getTree(),
                    $this->getEventType(),
                    $this->isVirtual(),
                    $this->getEventDateInterval()->toGedcomString(2),
                    $this->getLevel() + 1);
        }

        return null;
    }

    public static function sorterByLevel(): Closure {
        return function (PlaceStructure $x, PlaceStructure $y): int {
            return $x->getLevel() <=> $y->getLevel();
        };
    }
    
    //mainly for webtrees interfaces such as ModuleMapLinkInterface
    //note: MapLinkBing uses fact details to create a label
    //$label = strip_tags($fact->record()->fullName()) . ' — ' . $fact->label();
    public function asFact(): Fact {
        
        //TODO could use actual parent if constructed via Fact,
        //not considered to be important though        
        //would be nicer for MapLinkBing though
        
        $dummy = new PlaceAsTopLevelRecord(
            $this->gedcomName, 
            $this->tree());
        
        $label = MoreI18N::xlate('webtrees');
        if ($this->eventType !== null) {
            //TODO hacky, what about FAM events
            //$label = Registry::elementFactory()->make('INDI:'.$this->eventType)->label();
        }
        
        return new Fact(
            '1 EVEN'."\n".'2 TYPE '.$label."\n". $this->gedcom(), 
            $dummy, 
            'from PlaceStructure');
    }

}
