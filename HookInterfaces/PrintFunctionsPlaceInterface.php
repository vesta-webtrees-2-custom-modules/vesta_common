<?php

namespace Vesta\Hook\HookInterfaces;

use Fisharebest\Webtrees\Tree;
use Vesta\Model\GenericViewElement;
use Vesta\Model\GovReference;
use Vesta\Model\LocReference;
use Vesta\Model\MapCoordinates;
use Vesta\Model\PlaceStructure;

/**
 * Hooks for additional print functions on places
 *
 */
interface PrintFunctionsPlaceInterface {

  ////////////////////////////////////////////////////////////////////////////////

  public function plac2html(PlaceStructure $ps): ?GenericViewElement;

  public function gov2html(GovReference $gov, Tree $tree): ?GenericViewElement;

  public function map2html(MapCoordinates $map): ?GenericViewElement;

  public function loc2linkIcon(LocReference $loc): ?string;
}
