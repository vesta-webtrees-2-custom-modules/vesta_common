<?php

namespace Vesta\ControlPanelUtils\Model;

interface ControlPanelElement {

  /**
   * @return string|null
   */
  public function getDescription();

  /**
   * 
   * @return string
   */
  public function getSettingKey();
}
