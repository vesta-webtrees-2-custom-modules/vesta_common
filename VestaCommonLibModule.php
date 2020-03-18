<?php

namespace Vesta;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;

class VestaCommonLibModule extends AbstractModule implements ModuleCustomInterface {

  use VestaModuleCustomTrait;

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

  public function title(): string {
    return $this->vesta . ' ' . I18N::translate('Vesta Common');
  }

  public function description(): string {
    return I18N::translate('A module providing common classes for other \'Vesta\' custom modules. Does not have to be activated.');
  }

  /**
   * Where does this module store its resources
   *
   * @return string
   */
  public function resourcesFolder(): string {
    return __DIR__ . '/resources/';
  }

  /**
   * Additional/updated translations.
   *
   * @param string $language
   *
   * @return string[]
   */
  public function customTranslations(string $language): array {
    //TODO
    return [];
  }

}
