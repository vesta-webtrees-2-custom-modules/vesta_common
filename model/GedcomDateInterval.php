<?php

namespace Vesta\Model;

use Closure;
use Fisharebest\Webtrees\Carbon;
use Fisharebest\Webtrees\Date\AbstractCalendarDate;
use Fisharebest\Webtrees\Date\GregorianDate;
use Illuminate\Support\Collection;
use Vesta\Model\DateUtils;

/**
 * A date interval, convertible from/to gedcom DATE
 *   
 */
class GedcomDateInterval {
  
  /* @var $fromCalendarDate AbstractCalendarDate|null */
  private $fromCalendarDate;

  /* @var $toCalendarDate AbstractCalendarDate|null */
  private $toCalendarDate;

  protected static function minJD(AbstractCalendarDate $calendarDate): int {
    return $calendarDate->minimumJulianDay();
  }

  protected static function maxJD(AbstractCalendarDate $calendarDate): int {
    return $calendarDate->maximumJulianDay();
  }

  public function getFromCalendarDate(): ?AbstractCalendarDate {
    return $this->fromCalendarDate;
  }

  public function getToCalendarDate(): ?AbstractCalendarDate {
    return $this->toCalendarDate;
  }

  public function getFrom(): ?int {
    if ($this->fromCalendarDate === null) {
      return null;
    }
    return GedcomDateInterval::minJD($this->fromCalendarDate);
  }

  public function getTo(): ?int {
    if ($this->toCalendarDate === null) {
      return null;
    }
    return GedcomDateInterval::maxJD($this->toCalendarDate);
  }

  public function getMin(): ?int {
    if ($this->getFrom() !== null) {
      return $this->getFrom();
    }
    return $this->getTo();
  }

  public function getMedian(): ?int {
    if (($this->getFrom() !== null) && ($this->getTo() !== null)) {
      return ($this->getFrom() + $this->getTo()) / 2;
    }
    return $this->getMin();
  }

  public function __construct(?AbstractCalendarDate $fromCalendarDate, ?AbstractCalendarDate $toCalendarDate) {
    $this->fromCalendarDate = $fromCalendarDate;
    $this->toCalendarDate = $toCalendarDate;
  }

  public static function createEmpty() {
    return new GedcomDateInterval(null, null);
  }

  public static function createNow() {
    $startjd = Carbon::now()->julianDay();
    $endjd = $startjd;
    return new GedcomDateInterval(new GregorianDate($startjd), new GregorianDate($endjd));
  }
  
  /**
   * 
   * @param type $date
   * @param type $ignorePartialRanges if BEF/AFT are mainly used to indicate 'shortly before'/'shortly after',
   * it's often more helpful not to create actual (open) intervals
   * @return GedcomDateInterval
   */
  public static function create(string $date, $ignorePartialRanges = false): GedcomDateInterval {
    //cf Date.php
    // Extract any explanatory text
    if (preg_match('/^(.*) ?[(](.*)[)]/', $date, $match)) {
      $date = $match[1];
      //$this->text = $match[2];
    }

    if (preg_match('/^(FROM|BET) (.+) (AND|TO) (.+)/', $date, $match)) {
      //$qual1 = $match[1];
      $date1 = DateUtils::parseDate($match[2]);
      //$qual2 = $match[3];
      $date2 = DateUtils::parseDate($match[4]);

      return new GedcomDateInterval($date1, $date2);
    }

    if (preg_match('/^(TO|FROM|BEF|AFT|CAL|EST|INT|ABT) (.+)/', $date, $match)) {
      $qual1 = $match[1];
      $date1 = DateUtils::parseDate($match[2]);

      if ((!$ignorePartialRanges && ('BEF' === $qual1)) || ('TO' === $qual1)) {
        return new GedcomDateInterval(null, $date1);
      }

      if ((!$ignorePartialRanges && ('AFT' === $qual1)) || ('FROM' === $qual1)) {
        return new GedcomDateInterval($date1, null);
      }

      return new GedcomDateInterval($date1, $date1);
    }

    $date1 = DateUtils::parseDate($date);
    
    //handle invalid dates (e.g. via input '') as empty.
    if (($date1->minimumJulianDay() === 0) && ($date1->maximumJulianDay() === 0)) {
      return GedcomDateInterval::createEmpty();
    }
    
    return new GedcomDateInterval($date1, $date1);
  }

  /**
   * expand to single interval containing both original intervals (and any additional interval in between)
   * 
   * @return GedcomDateInterval
   */
  public function expand(GedcomDateInterval $other) {
    $fromCalendarDate = $this->getFromCalendarDate();
    if ($this->getFrom() !== null) {
      if (($other->getFrom() === null) || ($other->getFrom() < $this->getFrom())) {
        $fromCalendarDate = $other->getFromCalendarDate();
      }
    }

    $toCalendarDate = $this->getToCalendarDate();
    if ($this->getTo() !== null) {
      if (($other->getTo() === null) || ($other->getTo() > $this->getTo())) {
        $toCalendarDate = $other->getToCalendarDate();
      }
    }

    return new GedcomDateInterval($fromCalendarDate, $toCalendarDate);
  }

  /**
   * 
   * @param GedcomDateInterval $other
   * @param bool $sameInexactDateDoesNotIntersect interpret combination of 'TO 2001' and 'FROM 2001' as non-intersecting (instead of handling them like '31.12.2001' and '1.1.2001'),
   * (unless at least one of the intervals has same FROM/TO, because in that case we cannot guess the user's actual intention)
   * @return GedcomDateInterval|null
   */
  public function intersect(
          GedcomDateInterval $other,
          bool $sameInexactDateDoesNotIntersect = false): ?GedcomDateInterval {
    
    $fromCalendarDate = $this->getFromCalendarDate();
    if ($other->getFrom() !== null) {
      if (($this->getFrom() === null) || ($other->getFrom() > $this->getFrom())) {
        $fromCalendarDate = $other->getFromCalendarDate();
      }
    }

    $toCalendarDate = $this->getToCalendarDate();
    if ($other->getTo() !== null) {
      if (($this->getTo() === null) || ($other->getTo() < $this->getTo())) {
        $toCalendarDate = $other->getToCalendarDate();
      }
    }

    if (($fromCalendarDate !== null) && ($toCalendarDate !== null)) {
      if ($sameInexactDateDoesNotIntersect) {
        //do not use '===' for object equality!
        if ($fromCalendarDate == $toCalendarDate) {
          if ($fromCalendarDate->day() === 0) {
            
            //it is inexact, but do we have actual ranges?
            if ($this->getFromCalendarDate() !== $this->getToCalendarDate()) {
              if ($other->getFromCalendarDate() !== $other->getToCalendarDate()) {
                return null;
              }
            }
          }
        }
      }
      
      if (GedcomDateInterval::minJD($fromCalendarDate) > GedcomDateInterval::maxJD($toCalendarDate)) {    
        return null;
      }
    }

    return new GedcomDateInterval($fromCalendarDate, $toCalendarDate);
  }

  public function maxUntil(GedcomDateInterval $other): ?GedcomDateInterval {
    if ($other->getFrom() === null) {
      return null;
    }

    $fromCalendarDate = $this->getFromCalendarDate();
    if (($this->getFrom() !== null) && ($this->getFrom() > $other->getFrom())) {
      return null;
    }

    $toCalendarDate = $this->getToCalendarDate();
    if (($this->getTo() === null) || ($other->getFrom() < $this->getTo())) {
      $toCalendarDate = $other->getFromCalendarDate();
    }

    return new GedcomDateInterval($fromCalendarDate, $toCalendarDate);
  }

  /**
   * 
   * @param $other
   * @return part of this that is before $other
   */
  public function before(GedcomDateInterval $other): ?GedcomDateInterval {
    $otherFrom = $other->getFrom();
    if ($otherFrom === null) {
      return null;
    }
    
    $thisFrom = $this->getFrom();
    if ($thisFrom === null) {
      $retFrom = null;
    } else if ($thisFrom < $otherFrom) {
      $retFrom = $thisFrom;
    } else {
      return null;
    }
    
    $retTo = $otherFrom-1;
    return new GedcomDateInterval(
            ($retFrom == null)?null:new GregorianDate($retFrom), 
            new GregorianDate($retTo));
  }
  
  /**
   * 
   * @param $other
   * @return part of this that is after $other
   */
  public function after(GedcomDateInterval $other): ?GedcomDateInterval {
    $otherTo = $other->getTo();
    if ($otherTo === null) {
      return null;
    }
    
    $thisTo = $this->getTo();
    if ($thisTo === null) {
      $retTo = null;
    } else if ($thisTo > $otherTo) {
      $retTo = $thisTo;
    } else {
      return null;
    }
    
    $retFrom = $otherTo+1;
    return new GedcomDateInterval(
            new GregorianDate($retFrom), 
            ($retTo == null)?null:new GregorianDate($retTo));
  }
  
  /**
   * @param $asFromTo if false, return as BET a AND B (if applicable)
   * @return empty if (null, null), gedcom starting with newline otherwise 
   */
  public function toGedcomString($level, $asFromTo = false) {
    if (($this->fromCalendarDate === null) && ($this->toCalendarDate === null)) {
      return "";
    }

    if ($this->fromCalendarDate === null) {
      $to = DateUtils::toGedcomString($this->toCalendarDate);
      return "\n" . $level . " DATE BEF " . $to;
    }

    $from = DateUtils::toGedcomString($this->fromCalendarDate);
    if ($this->toCalendarDate === null) {
      return "\n" . $level . " DATE AFT " . $from;
    }

    //equality, not identity!
    if ($this->fromCalendarDate == $this->toCalendarDate) {
      return "\n" . $level . " DATE " . $from;
    }

    $to = DateUtils::toGedcomString($this->toCalendarDate);

    if ($asFromTo) {
      return "\n" . $level . " DATE FROM " . $from . " TO " . $to;
    }
    return "\n" . $level . " DATE BET " . $from . " AND " . $to;
  }

  /**
   * @return empty GedcomDateInterval if (null, null), GedcomDateInterval (year precision only) otherwise
   */
  public function shiftYears($fromPlusYears, $toPlusYears) {
    $shiftedFrom = null;
    if ($this->fromCalendarDate !== null) {
      $shiftedFrom = DateUtils::asYear($this->fromCalendarDate, $fromPlusYears);
    }
    $shiftedTo = null;
    if ($this->toCalendarDate !== null) {
      $shiftedTo = DateUtils::asYear($this->toCalendarDate, $toPlusYears);
    }

    return new GedcomDateInterval($shiftedFrom, $shiftedTo);
  }

  /**
   * 
   * 
   * @param Collection $input assumed to be ordered wrt GedcomDateInterval field
   * @return Collection that has an entry for each point within $this interval, ordered by GedcomDateInterval field
   */

  /**
   * example: 
   * input has [1-10],[3-20],[50-100],[300-400]
   * $this is [open-200]
   * 
   * result:
   * [open-1],[1-10],[3-20],[21-49],[50-100],[101-200]
   * 
   * @param Collection $input sorted by date
   * @param Closure $dateGetter arg: collection element type, return: GedcomDateInterval
   * @param Closure $elementCreator arg: GedcomDateInterval, return collection element type
   * @return Collection $input expanded so that each point within $this interval has an entry, ordered by GedcomDateInterval field
   * original inputs completely outside $this are skipped
   */  
  public function fillInterval(
          Collection $input,
          Closure $dateGetter,
          Closure $elementCreator): Collection {
    
    $ret = new Collection();
    
    $refInterval = $this;
    
    foreach ($input as $element) {
      /* @var $nextInterval GedcomDateInterval */
      $nextInterval = $dateGetter($element);
        
      if ($nextInterval->intersect($this) === null) {
        //irrelevant, skip
        continue;
      }
      
      if ($refInterval !== null) {
        $before = $refInterval->before($nextInterval);
        $refInterval = $refInterval->after($nextInterval);
        
        if ($before !== null) {
          $newElement = $elementCreator($before);
          $ret->add($newElement);
        }        
      } //else all covered, just add remaining
      
      $ret->add($element);
    }
    
    //final step
    if ($refInterval !== null) {
      $newElement = $elementCreator($refInterval);
      $ret->add($newElement);
    }
    
    return $ret;
  }
}
