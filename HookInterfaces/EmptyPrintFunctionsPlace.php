<?php

namespace Vesta\Hook\HookInterfaces;

use Fisharebest\Webtrees\Tree;
use Vesta\Model\GenericViewElement;
use Vesta\Model\GovReference;
use Vesta\Model\LocReference;
use Vesta\Model\MapCoordinates;
use Vesta\Model\PlaceStructure;

/**
 * base impl of PrintFunctionsPlaceInterface
 */
trait EmptyPrintFunctionsPlace {

  public function plac2html(PlaceStructure $ps): ?GenericViewElement {
    return null;
  }

  public function gov2html(GovReference $gov, Tree $tree): ?GenericViewElement {
    return null;
  }

  public function map2html(MapCoordinates $map): ?GenericViewElement {
    return null;
  }

  public function loc2linkIcon(LocReference $loc): ?string {
    return null;
  }
}
