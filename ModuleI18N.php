<?php

namespace Vesta;

use Fisharebest\Localization\Translator;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\ModuleInterface;


//cf I18N
//this is for disabled modules, which cannot translate via I18N
class ModuleI18N {
  
  public static function init(ModuleInterface $module): Translator {
    $locale = I18N::locale();

    // Add own translations only
    $translations = $module->customTranslations($locale->languageTag());

    // Create a translator
    $translator = new Translator($translations, $locale->pluralRule());
    
    return $translator;
  }
          
  public static function translate(ModuleInterface $module, string $message, ...$args): string {
    $message = self::init($module)->translate($message);

    return sprintf($message, ...$args);
  }
}
