<?php

namespace Vesta\Model;

use Closure;
use Fisharebest\Webtrees\Date\AbstractCalendarDate;
use Fisharebest\Webtrees\Date\GregorianDate;
use Fisharebest\Webtrees\Registry;
use Illuminate\Support\Collection;
use JsonSerializable;
use Vesta\Model\DateUtils;

/**
 * A date interval, convertible from/to gedcom DATE
 * (conversion loses inexactness information though, i.e. not strictly lossless)
 *
 */
class GedcomDateInterval implements JsonSerializable {
    /* @var $fromCalendarDate AbstractCalendarDate|null */

    //private $fromCalendarDate;

    /* @var $toCalendarDate AbstractCalendarDate|null */
    //private $toCalendarDate;

    /*
      public function getFromCalendarDate(): ?AbstractCalendarDate {
      return $this->fromCalendarDate;
      }

      public function getToCalendarDate(): ?AbstractCalendarDate {
      return $this->toCalendarDate;
      }
     */

    //simpler internal format

    /* @var $from int|null */
    private $from;

    /* @var $fromIsInexact bool */
    private $fromIsInexact;

    /* @var $to int|null */
    private $to;

    /* @var $toIsInexact bool */
    private $toIsInexact;

    #[\ReturnTypeWillChange]
    public function jsonSerialize() {
        return [
            'from' => $this->from,
            'fromIsInexact' => $this->fromIsInexact,
            'to' => $this->to,
            'toIsInexact' => $this->toIsInexact,
        ];
    }

    public function getFrom(): ?int {
        return $this->from;
        /*
          if ($this->fromCalendarDate === null) {
          return null;
          }
          return GedcomDateInterval::minJD($this->fromCalendarDate);
         */
    }

    public function getFromIsInexact(): bool {
        return $this->fromIsInexact;
    }

    public function getTo(): ?int {
        return $this->to;
        /*
          if ($this->toCalendarDate === null) {
          return null;
          }
          return GedcomDateInterval::maxJD($this->toCalendarDate);
         */
    }

    public function getToIsInexact(): bool {
        return $this->toIsInexact;
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

    public function __construct(
        ?int $from,
        ?int $to,
        bool $fromIsInexact = false,
        bool $toIsInexact = false) {

        //'to' cannot be smaller than 'from' in a meaningful interval
        //we rely on this e.g. when intersecting
        //if that is the case, we assume 'to' to be erroneous and just ignore it
        if (($from !== null) && ($to !== null) and ($to < $from)) {
            $to = null;
        }

        $this->from = $from;
        $this->to = $to;
        $this->fromIsInexact = ($from === null) ? true : $fromIsInexact;
        $this->toIsInexact = ($to === null) ? true : $toIsInexact;
    }

    public static function fromStd($std): GedcomDateInterval {
        return new GedcomDateInterval(
            $std->from,
            $std->to,
            $std->fromIsInexact,
            $std->toIsInexact);
    }

    protected static function minJD(AbstractCalendarDate $calendarDate): int {
        return $calendarDate->minimumJulianDay();
    }

    protected static function maxJD(AbstractCalendarDate $calendarDate): int {
        return $calendarDate->maximumJulianDay();
    }

    public static function createEmpty(): GedcomDateInterval {
        return new GedcomDateInterval(null, null);
    }

    public static function createNow(): GedcomDateInterval {
        $startjd = Registry::timestampFactory()->now()->julianDay();
        $endjd = $startjd;
        return new GedcomDateInterval($startjd, $endjd);
    }

    public static function createYear(int $year): GedcomDateInterval {
        $startjd = Registry::timestampFactory()->fromString($year, 'Y')->julianDay();
        $endjd = $startjd;
        return new GedcomDateInterval($startjd, $endjd);
    }

    public static function createFromCalendarDates(
        ?AbstractCalendarDate $fromCalendarDate,
        ?AbstractCalendarDate $toCalendarDate): GedcomDateInterval {

        $from = null;
        $fromIsInexact = false;
        if ($fromCalendarDate !== null) {
            $from = GedcomDateInterval::minJD($fromCalendarDate);
            $fromIsInexact = ($fromCalendarDate->day() === 0);
        }

        $to = null;
        $toIsInexact = false;
        if ($toCalendarDate !== null) {
            $to = GedcomDateInterval::maxJD($toCalendarDate);
            $toIsInexact = ($toCalendarDate->day() === 0);
        }

        return new GedcomDateInterval($from, $to, $fromIsInexact, $toIsInexact);
    }

    /**
     *
     * @param type $date
     * @param type $ignorePartialRanges if BEF/AFT are mainly used to indicate 'shortly before'/'shortly after',
     * it's often more helpful not to create actual (open) intervals
     * @return GedcomDateInterval
     */
    public static function create(
        string $date,
        $ignorePartialRanges = false): GedcomDateInterval {

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

            return GedcomDateInterval::createFromCalendarDates($date1, $date2);
        }

        if (preg_match('/^(TO|FROM|BEF|AFT|CAL|EST|INT|ABT) (.+)/', $date, $match)) {
            $qual1 = $match[1];
            $date1 = DateUtils::parseDate($match[2]);

            if ((!$ignorePartialRanges && ('BEF' === $qual1)) || ('TO' === $qual1)) {
                return GedcomDateInterval::createFromCalendarDates(null, $date1);
            }

            if ((!$ignorePartialRanges && ('AFT' === $qual1)) || ('FROM' === $qual1)) {
                return GedcomDateInterval::createFromCalendarDates($date1, null);
            }

            return GedcomDateInterval::createFromCalendarDates($date1, $date1);
        }

        $date1 = DateUtils::parseDate($date);

        //handle invalid dates (e.g. via input '') as empty.
        if (($date1->minimumJulianDay() === 0) && ($date1->maximumJulianDay() === 0)) {
            return GedcomDateInterval::createEmpty();
        }

        return GedcomDateInterval::createFromCalendarDates($date1, $date1);
    }

    /**
     * expand to single interval containing both original intervals (and any additional interval in between)
     *
     * @return GedcomDateInterval
     */
    public function expand(
        GedcomDateInterval $other) {

        $from = $this->getFrom();
        $fromIsInexact = $this->getFromIsInexact();
        if ($from !== null) {
            if (
                ($other->getFrom() === null) ||
                ($other->getFrom() < $from) ||
                ($fromIsInexact && ($other->getFrom() === $from))) {

                $from = $other->getFrom();
                $fromIsInexact = $other->getFromIsInexact();
            }
        }

        $to = $this->getTo();
        $toIsInexact = $this->getToIsInexact();
        if ($to !== null) {
            if (
                ($other->getTo() === null) ||
                ($other->getTo() > $to) ||
                ($toIsInexact && ($other->getTo() === $to))) {

                $to = $other->getTo();
                $toIsInexact = $other->getToIsInexact();
            }
        }

        return new GedcomDateInterval($from, $to, $fromIsInexact, $toIsInexact);
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

        $from = $this->getFrom();
        $fromIsInexact = $this->getFromIsInexact();
        if ($other->getFrom() !== null) {
            if (($from === null) || ($other->getFrom() > $from)) {
                $from = $other->getFrom();
                $fromIsInexact = $other->getFromIsInexact();
            }
        }

        $to = $this->getTo();
        $toIsInexact = $this->getToIsInexact();
        if ($other->getTo() !== null) {
            if (($to === null) || ($other->getTo() < $to)) {
                $to = $other->getTo();
                $toIsInexact = $other->getToIsInexact();
            }
        }

        if (($from !== null) && ($to !== null)) {
            if ($sameInexactDateDoesNotIntersect) {
                if ($from === $to) {
                    if ($fromIsInexact || $toIsInexact) {

                        //it is inexact, but do we have actual ranges?
                        if ($this->getFrom() !== $this->getTo()) {
                            if ($other->getFrom() !== $other->getTo()) {
                                return null;
                            }
                        }
                    }
                }
            }

            if ($from > $to) {
                return null;
            }
        }

        return new GedcomDateInterval($from, $to, $fromIsInexact, $toIsInexact);
    }

    public function maxUntil(
        GedcomDateInterval $other): ?GedcomDateInterval {

        if ($other->getFrom() === null) {
            return null;
        }

        $from = $this->getFrom();
        $fromIsInexact = $this->getFromIsInexact();
        if (($from !== null) && ($from > $other->getFrom())) {
            return null;
        }

        $to = $this->getTo();
        $toIsInexact = $this->getToIsInexact();
        if (($to === null) || ($other->getFrom() < $to)) {
            $to = $other->getFrom();
            $toIsInexact = $other->getFromIsInexact();
        }

        return new GedcomDateInterval($from, $to, $fromIsInexact, $toIsInexact);
    }

    /**
     *
     * @param $other
     * @return part of this that is before $other
     */
    public function before(
        GedcomDateInterval $other): ?GedcomDateInterval {

        $otherFrom = $other->getFrom();
        if ($otherFrom === null) {
            return null;
        }

        $thisFrom = $this->getFrom();
        $thisFromIsInexact = $this->getFromIsInexact();

        if ($thisFrom === null) {
            $retFrom = null;
            $retFromIsInexact = false;
        } else if ($thisFrom < $otherFrom) {
            $retFrom = $thisFrom;
            $retFromIsInexact = $thisFromIsInexact;
        } else {
            return null;
        }

        $retTo = $otherFrom - 1;
        return new GedcomDateInterval(
            $retFrom,
            $retTo,
            $retFromIsInexact,
            false);
    }

    /**
     *
     * @param $other
     * @return part of this that is after $other
     */
    public function after(
        GedcomDateInterval $other): ?GedcomDateInterval {

        $otherTo = $other->getTo();
        if ($otherTo === null) {
            return null;
        }

        $thisTo = $this->getTo();
        $thisToIsInexact = $this->getToIsInexact();

        if ($thisTo === null) {
            $retTo = null;
            $retToIsInexact = false;
        } else if ($thisTo > $otherTo) {
            $retTo = $thisTo;
            $retToIsInexact = $thisToIsInexact;
        } else {
            return null;
        }

        $retFrom = $otherTo + 1;
        return new GedcomDateInterval(
            $retFrom,
            $retTo,
            false,
            $retToIsInexact);
    }

    /**
     * @param $asFromTo if false, return as BET a AND B (if applicable)
     * @return empty if (null, null), gedcom starting with newline otherwise
     * note that we lose the inexactness information here! This is assumed to be acceptable.
     */
    public function toGedcomString(
        $level,
        $asFromTo = false) {

        if (($this->from === null) && ($this->to === null)) {
            return '';
        }

        if ($this->from === null) {
            $to = DateUtils::toGedcomString(new GregorianDate($this->to));
            if ($asFromTo) {
                return "\n" . $level . ' DATE TO ' . $to;
            }
            return "\n" . $level . ' DATE BEF ' . $to;
        }

        $from = DateUtils::toGedcomString(new GregorianDate($this->from));
        if ($this->to === null) {
            if ($asFromTo) {
                return "\n" . $level . ' DATE FROM ' . $from;
            }
            return "\n" . $level . ' DATE AFT ' . $from;
        }

        if ($this->from === $this->to) {
            return "\n" . $level . ' DATE ' . $from;
        }

        $to = DateUtils::toGedcomString(new GregorianDate($this->to));

        if ($asFromTo) {
            return "\n" . $level . ' DATE FROM ' . $from . ' TO ' . $to;
        }
        return "\n" . $level . ' DATE BET ' . $from . ' AND ' . $to;
    }

    /**
     * @return empty GedcomDateInterval if (null, null), GedcomDateInterval (year precision only) otherwise
     */
    public function shiftYears($fromPlusYears, $toPlusYears) {
        $shiftedFrom = null;
        if ($this->from !== null) {
            $shiftedFrom = GedcomDateInterval::minJD(
                    DateUtils::asYear(new GregorianDate($this->from), $fromPlusYears));
        }
        $shiftedTo = null;
        if ($this->to !== null) {
            $shiftedTo = GedcomDateInterval::maxJD(
                    DateUtils::asYear(new GregorianDate($this->to), $toPlusYears));
        }

        return new GedcomDateInterval($shiftedFrom, $shiftedTo, true, true);
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
