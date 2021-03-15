<?php

namespace Vesta;

use Cissee\WebtreesExt\AbstractModule;
use Cissee\WebtreesExt\Elements\FamilySearchFamilyTreeId;
use Cissee\WebtreesExt\Module\ModuleMetaInterface;
use Cissee\WebtreesExt\Module\ModuleMetaTrait;
use Cissee\WebtreesExt\MoreI18N;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\View;
use function GuzzleHttp\json_decode;

class VestaCommonLibModule extends AbstractModule implements 
  ModuleCustomInterface,
  ModuleMetaInterface {

  use ModuleCustomTrait, ModuleMetaTrait, VestaModuleCustomTrait {
    VestaModuleCustomTrait::customTranslations insteadof ModuleCustomTrait;
    ModuleMetaTrait::customModuleVersion insteadof ModuleCustomTrait;
    ModuleMetaTrait::customModuleLatestVersion insteadof ModuleCustomTrait;
  }

  private $vesta;

  public function __construct() {
    $this->vesta = json_decode('"\u26B6"');
  }

  public function customModuleAuthorName(): string {
    return 'Richard Cissée';
  }

  public function customModuleSupportUrl(): string {
    return 'https://cissee.de';
  }

  public function customModuleMetaDatasJson(): string {
    return file_get_contents(__DIR__ . '/metadata.json');
  } 
  
  public function customModuleLatestMetaDatasJsonUrl(): string {
    return 'https://raw.githubusercontent.com/vesta-webtrees-2-custom-modules/vesta_common/master/metadata.json';
  }
  
  public function resourcesFolder(): string {
    return __DIR__ . '/resources/';
  }
  
  public function title(): string {
    $title = CommonI18N::titleVestaCommon();
    return $this->vesta . ' ' . $title;
  }

  public function description(): string {
    $description = 
            I18N::translate('A module providing common classes and translations for other \'Vesta\' custom modules.') . ' ' .
            I18N::translate('This module cannot be disabled.');
    if (!$this->isEnabled()) {
      $description = ModuleI18N::translate($this, $description);
    }
    return $description;
  }
  
  public function boot(): void {
    $ef = Registry::elementFactory();
    $ef->register(['INDI:_FSFTID' => new FamilySearchFamilyTreeId(MoreI18N::xlate('FamilySearch id'))]);
  
    $this->flashWhatsNew('\Vesta\WhatsNew', 2);
    
    // Register a namespace for our views.
    View::registerNamespace($this->name(), $this->resourcesFolder() . 'views/');
    
    // Replace an existing view with our own version.
    View::registerCustomView('::admin/upgrade/wizard', $this->name() . '::admin/upgrade/wizard');      
  }
  
  public function isEnabled(): bool {
    //disabling this module has no useful effect, so we block it
    return true;
  }
}
