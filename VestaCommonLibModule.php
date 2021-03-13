<?php

namespace Vesta;

use Cissee\WebtreesExt\AbstractModule;
use Cissee\WebtreesExt\Elements\FamilySearchFamilyTreeId;
use Cissee\WebtreesExt\MoreI18N;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Registry;
use function GuzzleHttp\json_decode;

class VestaCommonLibModule extends AbstractModule implements ModuleCustomInterface {

  use ModuleCustomTrait, VestaModuleCustomTrait {
    VestaModuleCustomTrait::customTranslations insteadof ModuleCustomTrait;
    VestaModuleCustomTrait::customModuleLatestVersion insteadof ModuleCustomTrait;
  }

  private $vesta;

  public function __construct() {
    $this->vesta = json_decode('"\u26B6"');
  }

  public function customModuleAuthorName(): string {
    return 'Richard Cissée';
  }

  public function customModuleVersion(): string {
    return file_get_contents(__DIR__ . '/latest-version.txt');
  }

  public function customModuleLatestVersionUrl(): string {
    return 'https://raw.githubusercontent.com/vesta-webtrees-2-custom-modules/vesta_common/master/latest-version.txt';
  }

  public function customModuleSupportUrl(): string {
    return 'https://cissee.de';
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
  }
  
  public function isEnabled(): bool {
    //disabling this module has no useful effect, so we block it
    return true;
  }
}
