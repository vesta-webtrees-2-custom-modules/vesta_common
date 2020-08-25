<?php

namespace Vesta\Hook\HookInterfaces;

use Vesta\Model\GenericViewElement;

interface GovIdEditControlsInterface {

  public function govIdEditControlSelect2ScriptSnippet(): string;
          
  /**
   * 
   * @param string|null $govId
   * @param string $id
   * @param string $name
   * @param string $placeName
   * @param string|null $placeNameInputSelector
   * @param bool $forModal if true, this has to be prepared via ajax-modal-vesta.phtml plus govIdEditControlSelect2ScriptSnippet()!
   * @param bool $withLabel
   * @return GenericViewElement
   */
  public function govIdEditControl(
          ?string $govId, 
          string $id, 
          string $name, 
          string $placeName,
          ?string $placeNameInputSelector,          
          bool $forModal,
          bool $withLabel): GenericViewElement;
  
  public function govTypeIdEditControl(
          ?string $govTypeId, 
          string $id, 
          string $name): GenericViewElement;

}
