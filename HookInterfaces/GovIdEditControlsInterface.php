<?php

namespace Vesta\Hook\HookInterfaces;

use Vesta\Model\GenericViewElement;

interface GovIdEditControlsInterface {

  public function govIdEditControl(
          ?string $govId, 
          string $id, 
          string $name, 
          string $placeName, 
          bool $forModal,
          bool $withLabel): GenericViewElement;

}
