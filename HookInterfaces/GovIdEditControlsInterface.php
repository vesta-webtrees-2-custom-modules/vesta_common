<?php

namespace Vesta\Hook\HookInterfaces;

use Vesta\Model\GenericViewElement;

interface GovIdEditControlsInterface {

  public function govIdEditControl(?string $govId, string $label, bool $onCreate): GenericViewElement;

}
