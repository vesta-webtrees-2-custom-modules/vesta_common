<?php

namespace Vesta\Hook\HookInterfaces;

use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Individual;

interface ClippingsCartAddToCartInterface {
  
  public function doAddIndividualToCart(Individual $individual): void;
  
  public function doAddFamilyToCart(Family $family): void;
  
  public function doAddFamilyAndChildrenToCart(Family $family): void;
}
