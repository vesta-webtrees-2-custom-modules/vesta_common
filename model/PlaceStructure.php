<?php
namespace Vesta\Model;

use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Place;
use Vesta\Model\GedcomDateInterval;

/**
 * A GEDCOM level 2 place (PLAC) object (complete structure)
 * plus event type and date
 *   
 */
class PlaceStructure {

	private $tree;
	private $gedcom;
	private $eventType;
	private $eventDateInterval;
	
	// Regular expression to match a GEDCOM XREF.
	//cf WT_REGEX_XREF (1.x)/ Gedcom::REGEX_XREF (2.x)
	const REGEX_XREF = '[A-Za-z0-9:_-]+';
	
	public function getTree() {
		return $this->tree;
	}
	
	//discouraged, preferably use other getters! 
	public function getGedcom() {
		return $this->gedcom;
	}
	
	/**
	 * @return string|null tag of the level 1 event, if any
	 */	 	
	public function getEventType() {
		return $this->eventType;
	}

	/**
	 * @return GedcomDateInterval date interval of the level 1 event
	 */	 	
	public function getEventDateInterval() {
		return $this->eventDateInterval;
	}
	
	public function __construct($gedcom, Tree $tree, $eventType = null, GedcomDateInterval $eventDateInterval) {
		$this->gedcom = $gedcom;
		$this->tree = $tree;
		$this->eventType = $eventType;
		$this->eventDateInterval = $eventDateInterval;
	}
	
	public static function create($gedcom, Tree $tree, $eventType = null, $eventDateGedcomString = null) {
		$dateInterval = GedcomDateInterval::createEmpty();
		if ($eventDateGedcomString !== null) {
			$dateInterval = GedcomDateInterval::create($eventDateGedcomString);
		}
		return new PlaceStructure($gedcom, $tree, $eventType, $dateInterval);
	}
	
	/**
	 *
	 * @return Place	 
	 */	 	
	public function getPlace() {
		return new Place($this->getGedcomName(), $this->tree);
	}
	
	/**
	 * @return string|null
	 */ 	
	public function getLoc() {
		if (preg_match('/\n3 _LOC @(' . PlaceStructure::REGEX_XREF . ')@/', $this->getGedcom(), $match)) {
			return $match[1];
		}
		
		return null;
	}
	
	public function getLati() {
		//cf FunctionsPrint
		$map_lati = null;
		$cts = preg_match('/\d LATI (.*)/', $this->getGedcom(), $match);
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
		$cts = preg_match('/\d LONG (.*)/', $this->getGedcom(), $match);
		if ($cts > 0) {
			$map_long = $match[1];
		}
		if ($map_long) {
			$map_long = trim(strtr($map_long, "NSEW,�", " - -. ")); // E3.456� ==> 3.456
			return $map_long;
		}
		return null;	
	}
	
	public function getGedcomName() {
		if (preg_match('/2 (?:PLAC) ?(.*(?:(?:\n3 CONT ?.*)*)*)/', $this->gedcom, $match)) {
			return preg_replace("/\n3 CONT ?/", "\n", $match[1]);
		}
		return null;
	}
	
	//TODO
	//public function getGov()
	
	public function getAttribute($tag) {
		if (preg_match('/3 (?:' . $tag . ') ?(.*(?:(?:\n4 CONT ?.*)*)*)/', $this->gedcom, $match)) {
			return preg_replace("/\n4 CONT ?/", "\n", $match[1]);
		}
		return null;
	}
}
