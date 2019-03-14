<?php

namespace Cissee\WebtreesExt;

use Fisharebest\Webtrees\Bootstrap4;

class ViewUtils {
  //webtrees 1.x had FunctionsEdit::twoStateCheckbox

  /**
   * 
   * @param string $name
   * @param boolean $value
   * @param string $label
   * @param boolean $disabled
   * @param boolean $inline
   */
  public static function checkbox($name, $value, $label, $disabled = false, $inline = false) {
    return Bootstrap4::checkbox(
                    $label,
                    $inline,
                    [
                        'name' => $name,
                        'checked' => (bool) $value,
                        'disabled' => $disabled
    ]);
  }

  //webtrees 1.x had FunctionsEdit::selectEditControl

  /**
   * 
   * @param string $name
   * @param string[] $options
   * @param string $value
   * @param string $label
   */
  public static function select($name, $options, $value) {
    return Bootstrap4::select(
                    $options,
                    $value,
                    [
                        'id' => $name,
                        'name' => $name]);
  }

}
