<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt;

use Closure;
use Fisharebest\Webtrees\Date;
use Fisharebest\Webtrees\Fact;
use Illuminate\Support\Collection;
use function usort;

class FactExt {

    /**
     * A multi-key sort
     * 1. First divide the facts into two arrays one set with dates and one set without dates
     * 2. Sort each of the two new arrays, the date using the compare date function, the non-dated
     * using the compare type function
     * 3. Then merge the arrays
     * //[RC] adjustment to the original method: just merge in this order, no further adjustments per type!
     *
     *
     * @param Collection<int,Fact> $unsorted
     *
     * @return Collection<int,Fact>
     */
    public static function sortFacts(Collection $unsorted): Collection
    {
        $dated    = [];
        $nondated = [];
        $sorted   = [];

        // Split the array into dated and non-dated arrays
        $order = 0;

        foreach ($unsorted as $fact) {
            $fact->sortOrder = $order;
            $order++;

            if ($fact->date()->isOK()) {
                $dated[] = $fact;
            } else {
                $nondated[] = $fact;
            }
        }

        usort($dated, self::dateComparator());
        usort($nondated, Fact::typeComparator());

        // Merge the arrays
        foreach ($dated as $fact) {
            $sorted[] = $fact;
        }

        foreach ($nondated as $fact) {
            $sorted[] = $fact;
        }

        return new Collection($sorted);
    }

    /**
     * Helper functions to sort facts
     * same as in Fact, but it's private there bah
     *
     * we have to adjust anyway for overlapping dates:
     * just sort strictly by start date
     *
     * @return Closure
     */
    public static function dateComparator(): Closure
    {
        return static function (Fact $a, Fact $b): int {
            if ($a->date()->isOK() && $b->date()->isOK()) {
                // If both events have dates, compare by date

                //[RC] adjusted: Date::compare is not what we want for overlapping events
                $ret = FactExt::compare($a->date(), $b->date());

                if ($ret === 0) {
                    // If dates overlap, compare by fact type
                    $ret = Fact::typeComparator()($a, $b);

                    // If the fact type is also the same, retain the initial order
                    if ($ret === 0) {
                        $ret = $a->sortOrder <=> $b->sortOrder;
                    }
                }

                return $ret;
            }

            // One or both events have no date - retain the initial order
            return $a->sortOrder <=> $b->sortOrder;
        };
    }

    //adapted from Date.php
    public static function compare(Date $a, Date $b): int
    {
        // Get min/max JD for each date.
        switch ($a->qual1) {
            case 'BEF':
                $amin = $a->minimumJulianDay() - 1;
                $amax = $amin;
                break;
            case 'AFT':
                $amax = $a->maximumJulianDay() + 1;
                $amin = $amax;
                break;
            default:
                $amin = $a->minimumJulianDay();
                $amax = $a->maximumJulianDay();
                break;
        }
        switch ($b->qual1) {
            case 'BEF':
                $bmin = $b->minimumJulianDay() - 1;
                $bmax = $bmin;
                break;
            case 'AFT':
                $bmax = $b->maximumJulianDay() + 1;
                $bmin = $bmax;
                break;
            default:
                $bmin = $b->minimumJulianDay();
                $bmax = $b->maximumJulianDay();
                break;
        }

        if ($amin < $bmin) {
            return -1;
        }

        if ($amin > $bmin) {
            return 1;
        }

        //if equal min: shorter first
        return ($amax <=> $bmax);
    }
}
