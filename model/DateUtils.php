<?php

namespace Vesta\Model;

use DomainException;
use Fisharebest\ExtCalendar\GregorianCalendar;
use Fisharebest\Webtrees\Date\AbstractCalendarDate;
use Fisharebest\Webtrees\Date\CalendarDate;
use Fisharebest\Webtrees\Date\FrenchDate;
use Fisharebest\Webtrees\Date\GregorianDate;
use Fisharebest\Webtrees\Date\HijriDate;
use Fisharebest\Webtrees\Date\JalaliDate;
use Fisharebest\Webtrees\Date\JewishDate;
use Fisharebest\Webtrees\Date\JulianDate;
use Fisharebest\Webtrees\Date\RomanDate;

class DateUtils {
  //cf Date.php where this in non-static (why?) and private (why why why?)

  /**
   * Convert a calendar date, such as "12 JUN 1943" into calendar date object.
   * A GEDCOM date range may have two calendar dates.
   *
   * @param string $date
   *
   * @throws DomainException
   * @return AbstractCalendarDate
   */
  public static function parseDate($date): AbstractCalendarDate {
    // Valid calendar escape specified? - use it
    if (preg_match('/^(@#D(?:GREGORIAN|JULIAN|HEBREW|HIJRI|JALALI|FRENCH R|ROMAN)+@) ?(.*)/', $date, $match)) {
        $cal  = $match[1];
        $date = $match[2];
    } else {
        $cal = '';
    }
    // A date with a month: DM, M, MY or DMY
    if (preg_match('/^(\d?\d?) ?(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC|TSH|CSH|KSL|TVT|SHV|ADR|ADS|NSN|IYR|SVN|TMZ|AAV|ELL|VEND|BRUM|FRIM|NIVO|PLUV|VENT|GERM|FLOR|PRAI|MESS|THER|FRUC|COMP|MUHAR|SAFAR|RABI[AT]|JUMA[AT]|RAJAB|SHAAB|RAMAD|SHAWW|DHUAQ|DHUAH|FARVA|ORDIB|KHORD|TIR|MORDA|SHAHR|MEHR|ABAN|AZAR|DEY|BAHMA|ESFAN) ?((?:\d{1,4}(?: B\.C\.)?|\d\d\d\d\/\d\d)?)$/', $date, $match)) {
        $d = $match[1];
        $m = $match[2];
        $y = $match[3];
    } elseif (preg_match('/^(\d{1,4}(?: B\.C\.)?|\d\d\d\d\/\d\d)$/', $date, $match)) {
        // A date with just a year
        $d = '';
        $m = '';
        $y = $match[1];
    } else {
        // An invalid date - do the best we can.
        $d = '';
        $m = '';
        $y = '';
        // Look for a 3/4 digit year anywhere in the date
        if (preg_match('/\b(\d{3,4})\b/', $date, $match)) {
            $y = $match[1];
        }
        // Look for a month anywhere in the date
        if (preg_match('/(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC|TSH|CSH|KSL|TVT|SHV|ADR|ADS|NSN|IYR|SVN|TMZ|AAV|ELL|VEND|BRUM|FRIM|NIVO|PLUV|VENT|GERM|FLOR|PRAI|MESS|THER|FRUC|COMP|MUHAR|SAFAR|RABI[AT]|JUMA[AT]|RAJAB|SHAAB|RAMAD|SHAWW|DHUAQ|DHUAH|FARVA|ORDIB|KHORD|TIR|MORDA|SHAHR|MEHR|ABAN|AZAR|DEY|BAHMA|ESFAN)/', $date, $match)) {
            $m = $match[1];
            // Look for a day number anywhere in the date
            if (preg_match('/\b(\d\d?)\b/', $date, $match)) {
                $d = $match[1];
            }
        }
    }

    // Unambiguous dates - override calendar escape
    if (preg_match('/^(TSH|CSH|KSL|TVT|SHV|ADR|ADS|NSN|IYR|SVN|TMZ|AAV|ELL)$/', $m)) {
        $cal = JewishDate::ESCAPE;
    } elseif (preg_match('/^(VEND|BRUM|FRIM|NIVO|PLUV|VENT|GERM|FLOR|PRAI|MESS|THER|FRUC|COMP)$/', $m)) {
        $cal = FrenchDate::ESCAPE;
    } elseif (preg_match('/^(MUHAR|SAFAR|RABI[AT]|JUMA[AT]|RAJAB|SHAAB|RAMAD|SHAWW|DHUAQ|DHUAH)$/', $m)) {
        $cal = HijriDate::ESCAPE; // This is a WT extension
    } elseif (preg_match('/^(FARVA|ORDIB|KHORD|TIR|MORDA|SHAHR|MEHR|ABAN|AZAR|DEY|BAHMA|ESFAN)$/', $m)) {
        $cal = JalaliDate::ESCAPE; // This is a WT extension
    } elseif (preg_match('/^\d{1,4}( B\.C\.)|\d\d\d\d\/\d\d$/', $y)) {
        $cal = JulianDate::ESCAPE;
    }

    // Ambiguous dates - don't override calendar escape
    if ($cal === '') {
        if (preg_match('/^(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)$/', $m)) {
            $cal =  GregorianDate::ESCAPE;
        } elseif (preg_match('/^[345]\d\d\d$/', $y)) {
            // Year 3000-5999
            $cal = JewishDate::ESCAPE;
        } else {
            $cal = GregorianDate::ESCAPE;
        }
    }
    // Now construct an object of the correct type
    switch ($cal) {
        case GregorianDate::ESCAPE:
            return new GregorianDate([
                $y,
                $m,
                $d,
            ]);
        case JulianDate::ESCAPE:
            return new JulianDate([
                $y,
                $m,
                $d,
            ]);
        case JewishDate::ESCAPE:
            return new JewishDate([
                $y,
                $m,
                $d,
            ]);
        case HijriDate::ESCAPE:
            return new HijriDate([
                $y,
                $m,
                $d,
            ]);
        case FrenchDate::ESCAPE:
            return new FrenchDate([
                $y,
                $m,
                $d,
            ]);
        case JalaliDate::ESCAPE:
            return new JalaliDate([
                $y,
                $m,
                $d,
            ]);
        case RomanDate::ESCAPE:
            return new RomanDate([
                $y,
                $m,
                $d,
            ]);
        default:
            throw new DomainException('Invalid calendar');
    }
  }

  //[RC] added 

  /**
   * 
   * @param CalendarDate $date
   * @param int $offset
   * @return CalendarDate
   */
  public static function asYear(AbstractCalendarDate $date, $offset = 0) {
    //TODO: use this
    //$date->nextYear();

    $year = $date->year() + $offset;

    if ($date instanceof GregorianDate) {
      return new GregorianDate([''.$year, '', '']);
    }
    if ($date instanceof JulianDate) {
      return new JulianDate([''.$year, '', '']);
    }
    if ($date instanceof JewishDate) {
      return new JewishDate([''.$year, '', '']);
    }
    if ($date instanceof FrenchDate) {
      return new FrenchDate([''.$year, '', '']);
    }
    if ($date instanceof HijriDate) {
      return new HijriDate([''.$year, '', '']);
    }
    if ($date instanceof JalaliDate) {
      return new JalaliDate([''.$year, '', '']);
    }

    //fallback
    return new GregorianDate([''.$year, '', '']);
  }

  //[RC] added
  public static function toGedcomString(AbstractCalendarDate $calendarDate) {
    //ok so this was available all the time apparently, just rather hard to find out about it!
    if ($calendarDate instanceof GregorianDate) {
      $ret = $calendarDate->format('%A %O %E');
    } else {
      $ret = $calendarDate->format('%@ %A %O %E');      
    }
            
    //error_log("toGedComString".$ret);
    
    return $ret;

    /*
    if (($calendarDate instanceof GregorianDate) || ($calendarDate instanceof JulianDate)) {
      $str = '';
      if ($calendarDate instanceof JulianDate) {
        $str = '@#DJULIAN@ ';
      }
      if ($calendarDate->day() !== 0) {
        $str .= $calendarDate->day() . " ";
      }
      if ($calendarDate->month() !== 0) {
        switch ($calendarDate->month()) {
          case 1: $m = "JAN";
            break;
          case 2: $m = "FEB";
            break;
          case 3: $m = "MAR";
            break;
          case 4: $m = "APR";
            break;
          case 5: $m = "MAY";
            break;
          case 6: $m = "JUN";
            break;
          case 7: $m = "JUL";
            break;
          case 8: $m = "AUG";
            break;
          case 9: $m = "SEP";
            break;
          case 10: $m = "OCT";
            break;
          case 11: $m = "NOV";
            break;
          case 12: $m = "DEC";
            break;
          default:
            throw new DomainException('Unexpected month');
        }
        $str .= $m . " ";
      }
      $str .= $calendarDate->year();
      error_log("toGedcomString".$str);
      return $str;
    }
    */
  }

  //[RC] added, discouraged
  public static function julianDayToGedcomString($jd) {
    $gregorian_calendar = new GregorianCalendar;
    $ymd = $gregorian_calendar->jdToYmd($jd);

    switch ($ymd[1]) {
      case 1: $m = "JAN";
        break;
      case 2: $m = "FEB";
        break;
      case 3: $m = "MAR";
        break;
      case 4: $m = "APR";
        break;
      case 5: $m = "MAY";
        break;
      case 6: $m = "JUN";
        break;
      case 7: $m = "JUL";
        break;
      case 8: $m = "AUG";
        break;
      case 9: $m = "SEP";
        break;
      case 10: $m = "OCT";
        break;
      case 11: $m = "NOV";
        break;
      case 12: $m = "DEC";
        break;
      default:
        throw new DomainException('Unexpected month');
    }

    $str1 = $ymd[2] . " " . $m . " " . $ymd[0];
    return $str1;
  }

}
