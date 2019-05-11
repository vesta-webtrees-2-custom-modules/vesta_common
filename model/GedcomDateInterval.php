<?php

namespace Vesta\Model;

use Fisharebest\Webtrees\Date\AbstractCalendarDate;
use Vesta\Model\DateUtils;

/**
 * A date interval, convertible from/to gedcom DATE
 *   
 */
class GedcomDateInterval {
  
  /* @var $fromCalendarDate AbstractCalendarDate */
  private $fromCalendarDate;

  /* @var $toCalendarDate AbstractCalendarDate */
  private $toCalendarDate;

  protected static function minJD($calendarDate) {
    if (method_exists($calendarDate, "minimumJulianDay")) {
      return $calendarDate->minimumJulianDay();
    }

    //1.7.x
    return $calendarDate->minJD;
  }

  protected static function maxJD($calendarDate) {
    if (method_exists($calendarDate, "maximumJulianDay")) {
      return $calendarDate->maximumJulianDay();
    }

    //1.7.x
    return $calendarDate->maxJD;
  }

  /**
   *
   * @return CalendarDate|null	 
   */
  public function getFromCalendarDate() {
    return $this->fromCalendarDate;
  }

  /**
   *
   * @return CalendarDate|null	 
   */
  public function getToCalendarDate() {
    return $this->toCalendarDate;
  }

  /**
   *
   * @return integer|null	 
   */
  public function getFrom() {
    if ($this->fromCalendarDate === null) {
      return null;
    }
    return GedcomDateInterval::minJD($this->fromCalendarDate);
  }

  /**
   *
   * @return integer|null	 
   */
  public function getTo() {
    if ($this->toCalendarDate === null) {
      return null;
    }
    return GedcomDateInterval::maxJD($this->toCalendarDate);
  }

  public function getMin() {
    if ($this->getFrom() !== null) {
      return $this->getFrom();
    }
    return $this->getTo();
  }

  public function getMedian() {
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

  /**
   * 
   * @param type $date
   * @param type $ignorePartialRanges if BEF/AFT are mainly used to indicate 'shortly before'/'shortly after',
   * it's often more helpful not to create actual (open) intervals
   * @return GedcomDateInterval
   */
  public static function create($date, $ignorePartialRanges = false) {
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
   * @return GedcomDateInterval|null 
   */
  public function intersect(GedcomDateInterval $other) {
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

    //TODO check: do imprecise dates intersect correctly?
    if (($fromCalendarDate !== null) && ($toCalendarDate !== null) && (GedcomDateInterval::minJD($fromCalendarDate) > GedcomDateInterval::maxJD($toCalendarDate))) {
      return null;
    }

    return new GedcomDateInterval($fromCalendarDate, $toCalendarDate);
  }

  public function maxUntil(GedcomDateInterval $other) {
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

}
