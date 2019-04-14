<?php

namespace Cissee\WebtreesExt;

use function view;

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
    if ($inline) {
      return view('components/checkbox-inline', ['label' => $label, 'name' => $name, 'checked' => $value, 'disabled' => $disabled]);
    }
    return view('components/checkbox', ['label' => $label, 'name' => $name, 'checked' => $value, 'disabled' => $disabled]);
  }

  /**
   * 
   * @param string $name
   * @param string[] $options
   * @param string $value
   * @param string $label
   */
  public static function select($name, $options, $value) {
    return view('components/select', ['name' => $name, 'selected' => $value, 'options' => $options]);
  }

}
