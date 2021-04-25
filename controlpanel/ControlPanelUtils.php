<?php

namespace Vesta\ControlPanelUtils;

use Cissee\WebtreesExt\MoreI18N;
use Cissee\WebtreesExt\ViewUtils;
use Exception;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Psr\Http\Message\ServerRequestInterface;
use Vesta\ControlPanelUtils\Model\ControlPanelCheckbox;
use Vesta\ControlPanelUtils\Model\ControlPanelCheckboxInverted;
use Vesta\ControlPanelUtils\Model\ControlPanelElement;
use Vesta\ControlPanelUtils\Model\ControlPanelFactRestriction;
use Vesta\ControlPanelUtils\Model\ControlPanelPreferences;
use Vesta\ControlPanelUtils\Model\ControlPanelRadioButtons;
use Vesta\ControlPanelUtils\Model\ControlPanelRange;
use Vesta\ControlPanelUtils\Model\ControlPanelSection;
use Vesta\ControlPanelUtils\Model\ControlPanelSubsection;
use Vesta\ControlPanelUtils\Model\ControlPanelTextbox;
use function csrf_field;
use function view;

class ControlPanelUtils {

  private $module;

  /**
   * 
   * @param ModuleInterface $module
   */
  public function __construct(ModuleInterface $module) {
    $this->module = $module;
  }

  /**
   * 
   * @return void
   */
  public function printPrefs(ControlPanelPreferences $prefs, $module) {
    ?>
    <h1><?php echo MoreI18N::xlate('Preferences'); ?></h1>

    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="route" value="module">
        <input type="hidden" name="module" value="<?php echo $module; ?>">
        <input type="hidden" name="action" value="Admin">
        <?php
        foreach ($prefs->getSections() as $section) {
          $this->printSection($section);
        }
        ?>

        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-check"></i>
                    <?php echo MoreI18N::xlate('save'); ?>
                </button>
            </div>
        </div>
    </form>
    <?php
  }

  /**
   * 
   * @return void
   */
  public function printSection(ControlPanelSection $section) {
    ?>
    <h3><?php echo $section->getLabel(); ?></h3>
    <?php
    $description = $section->getDescription();
    if ($description !== null) {
      ?>
      <p class="small text-muted">
          <?php echo $description; ?>
      </p>
      <?php
    }
    foreach ($section->getSubsections() as $subsection) {
      $this->printSubsection($subsection);
    }
  }

  /**
   * 
   * @return void
   */
  public function printSubsection(ControlPanelSubsection $subsection) {
    ?>
    <div class="row form-group">
        <label class="col-form-label col-sm-3">
            <?php echo $subsection->getLabel(); ?>
        </label>
        <div class="col-sm-9">
            <?php
            foreach ($subsection->getElements() as $element) {
              $this->printElement($element);
            }
            $description = $subsection->getDescription();
            if ($description !== null) {
              ?>
              <p class="small text-muted">
                  <?php echo $description; ?>
              </p>
              <?php
            }
            ?>
        </div>
    </div>
    <?php
  }

  public function printElement(ControlPanelElement $element) {
    if ($element instanceof ControlPanelTextbox) {
      $this->printControlPanelTextbox($element);
    } else if ($element instanceof ControlPanelCheckbox) {
      $this->printControlPanelCheckbox($element);
    } else if ($element instanceof ControlPanelCheckboxInverted) {
      $this->printControlPanelCheckboxInverted($element);
    } else if ($element instanceof ControlPanelFactRestriction) {
      $this->printControlPanelFactRestriction($element);
    } else if ($element instanceof ControlPanelRange) {
      $this->printControlPanelRange($element);
    } else if ($element instanceof ControlPanelRadioButtons) {
      $this->printControlPanelRadioButtons($element);
    } else {
      throw new Exception("unsupported ControlPanelElement");
    }

    $description = $element->getDescription();
    if ($description !== null) {
      ?>
      <p class="small text-muted">
          <?php echo $description; ?>
      </p>
      <?php
    }
  }

  public function printControlPanelTextbox(ControlPanelTextbox $element) {
    $value = $this->module->getPreference($element->getSettingKey(), $element->getSettingDefaultValue());
    $maxLength = $element->getMaxLength();
    $maxLengthStr = '';
    if ($maxLength > 0) {
      $maxLengthStr = 'maxlength="' . $maxLength .'" ';
    }
    
    $pattern = $element->getPattern();
    $patternStr = '';
    if ($pattern !== null) {
      $patternStr = 'pattern="' . $pattern .'" ';
    }
    
    $required = $element->getRequired();
    $requiredStr = '';
    if ($required === true) {
      $requiredStr = 'required ';
    }
    
    ?>
      <div class="col-sm-10">
          <div class="input-group" dir="ltr">
              <div class="input-group-prepend">
                  <span class="input-group-text" dir="ltr">
                      <?= e($element->getLabel()) ?>
                  </span>
              </div>
              <input class="form-control" id="<?= $element->getSettingKey() ?>" <?= $maxLengthStr ?>name="<?= $element->getSettingKey() ?>" <?= $patternStr?><?= $requiredStr?>type="text" value="<?= e($value) ?>" dir="ltr">
          </div>
      </div>
    <?php 
  }
  
  public function printControlPanelCheckbox(ControlPanelCheckbox $element) {
    $value = $this->module->getPreference($element->getSettingKey(), $element->getSettingDefaultValue());

    //ugly positioning of checkbox - for now, build checkbox directly (as in admin_trees_config)
    /*
      ?>
      <div class="optionbox">
      <?php echo ViewUtils::checkbox($element->getSettingKey(), $value, $element->getLabel()); ?>
      </div>
      <?php
     */
    ?>
    <div class="form-check">
        <label for="<?= $element->getSettingKey() ?>">
            <input name="<?= $element->getSettingKey() ?>" type="checkbox" id="<?= $element->getSettingKey() ?>" value="<?= $element->getSettingKey() ?>" <?= $value ? 'checked' : '' ?>>
            <?= $element->getLabel() ?>
        </label>
    </div>
    <?php
  }
  
  public function printControlPanelCheckboxInverted(ControlPanelCheckboxInverted $element) {
    $value = $this->module->getPreference($element->getSettingKey(), $element->getSettingDefaultValue());

    //ugly positioning of checkbox - for now, build checkbox directly (as in admin_trees_config)
    /*
      ?>
      <div class="optionbox">
      <?php echo ViewUtils::checkbox($element->getSettingKey(), $value, $element->getLabel()); ?>
      </div>
      <?php
     */
    ?>
    <div class="form-check">
        <label for="<?= $element->getSettingKey() ?>">
            <input name="<?= $element->getSettingKey() ?>" type="checkbox" id="<?= $element->getSettingKey() ?>" value="<?= $element->getSettingKey() ?>" <?= $value ? '' : 'checked' ?>>
            <?= $element->getLabel() ?>
        </label>
    </div>
    <?php
  }
  
  public function printControlPanelFactRestriction(ControlPanelFactRestriction $element) {
    //why escape only here?	
    $value = e($this->module->getPreference($element->getSettingKey(), $element->getSettingDefaultValue()));
    /*
    ?>
    <div class="col-sm-9">
        <?= Bootstrap4::multiSelect(
     GedcomTag::getPicklistFacts($element->getFamily() ? 'FAM' : 'INDI'), 
     explode(',', $value), 
     [
     'id' => $element->getSettingKey(), 
     'name' => $element->getSettingKey() . '[]', 
     'class' => 'select2']) ?>
    </div>
    <?php
    */
    echo view('components/select', [
        'name' => $element->getSettingKey() . '[]', 
        'id' => $element->getSettingKey(), 
        'selected' => explode(',', $value), 
        'options' => $element->getOptions(), 
        'class' => 'select2']);
  }

  public function printControlPanelRange(ControlPanelRange $element) {
    $value = (int)$this->module->getPreference($element->getSettingKey(), $element->getSettingDefaultValue());
    ?>
    <div class="input-group">
        <div class="input-group col-sm-4">
          <label class="input-group-addon" for="<?php echo $element->getSettingKey(); ?>"><?php echo $element->getLabel() ?></label>
        </div>
        <?php echo ViewUtils::select($element->getSettingKey(), array_combine(range($element->getMin(), $element->getMax()), range($element->getMin(), $element->getMax())), $value) ?>
    </div>
    <?php
  }

  public function printControlPanelRadioButtons(ControlPanelRadioButtons $element) {
    if ($element->getInline()) {
      $this->printControlPanelRadioButtonsInline($element);
      return;
    }

    $value = $this->module->getPreference($element->getSettingKey(), $element->getSettingDefaultValue());
    foreach ($element->getValues() as $radioButton) {
      ?>
      <label>
          <input type="radio" name="<?php echo $element->getSettingKey(); ?>" value="<?php echo $radioButton->getValue(); ?>" <?php echo ($value === $radioButton->getValue()) ? 'checked' : ''; ?>>
          <?php echo $radioButton->getLabel(); ?>
      </label>
      <br>
      <?php
      $description = $radioButton->getDescription();
      if ($description !== null) {
        ?>
        <p class="small text-muted">
            <?php echo $description; ?>
        </p>
        <?php
      }
    }
  }

  public function printControlPanelRadioButtonsInline(ControlPanelRadioButtons $element) {
    $options = array();
    foreach ($element->getValues() as $value) {
      $options[$value->getValue()] = $value->getLabel();
      //note: description, if any, not displayed in inline mode!
    }

    $value = $this->module->getPreference($element->getSettingKey(), $element->getSettingDefaultValue());

    //problematic because array keys may be coverted to integer by php (even if explicitly set as string)
    //echo view('components/radios-inline', ['name' => $element->getSettingKey(), 'selected' => $value, 'options' => $options]);
    
    //fixed contents:
    foreach ($options as $optionValue => $label) {
      echo view('components/radio-inline', ['label' => $label, 'name' => $element->getSettingKey(), 'value' => (string)$optionValue, 'checked' => (string)$optionValue === $value]);
    }
  }

  /**
   * 
   * @return void
   */
  public function savePostData(ServerRequestInterface $request, ControlPanelPreferences $prefs) {
    foreach ($prefs->getSections() as $section) {
      foreach ($section->getSubsections() as $subsection) {
        foreach ($subsection->getElements() as $element) {
          if ($element instanceof ControlPanelFactRestriction) {
            $value = '';
            if (array_key_exists($element->getSettingKey(), $request->getParsedBody())) {
              $value = implode(',', $request->getParsedBody()[$element->getSettingKey()]);
            }
            $this->module->setPreference($element->getSettingKey(), $value);
          } else if ($element instanceof ControlPanelCheckbox) {
            $this->module->setPreference($element->getSettingKey(), (($request->getParsedBody()[$element->getSettingKey()] ?? null) != null)?'1':'0');
          } else if ($element instanceof ControlPanelCheckboxInverted) {
            $this->module->setPreference($element->getSettingKey(), (($request->getParsedBody()[$element->getSettingKey()] ?? null) != null)?'0':'1');
          } else {
            $this->module->setPreference($element->getSettingKey(), $request->getParsedBody()[$element->getSettingKey()]);
          }
        }
      }
    }

    FlashMessages::addMessage(MoreI18N::xlate('The preferences for the module “%s” have been updated.', $this->module->title()), 'success');
  }

}
