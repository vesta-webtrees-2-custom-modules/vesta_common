<?php

namespace Vesta\Hook\HookInterfaces;

use Vesta\Model\GenericViewElement;

interface GovIdEditControlsInterface {

  public function govIdEditControlSelectScriptSnippet(): string;

  /**
   *
   * @param string|null $govId
   * @param string $id
   * @param string $name
   * @param string $placeName
   * @param string|null $placeNameSelector
   * @param bool $forModal if true, this has to be prepared via ajax-modal-vesta.phtml plus govIdEditControlSelectScriptSnippet()!
   * @param bool $withLabel
   * @return GenericViewElement
   */
  public function govIdEditControl(
          ?string $govId,
          string $id,
          string $name,
          string $placeName,
          ?string $placeNameSelector,
          bool $forModal,
          bool $withLabel): GenericViewElement;

  public function govTypeIdEditControl(
          ?string $govTypeId,
          string $id,
          string $name): GenericViewElement;

}
