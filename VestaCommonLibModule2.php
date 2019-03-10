<?php

namespace Vesta;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;

class VestaCommonLibModule2 extends AbstractModule implements ModuleCustomInterface {

	private $vesta;
	
	public function __construct() {
		$this->vesta = json_decode('"\u26B6"');
	}
	
	public function customModuleAuthorName(): string {
		return 'Richard Cissée';
	}

	public function customModuleVersion(): string {
		return '2.0.0-alpha.5.1';
	}
	
	public function customModuleLatestVersionUrl(): string {
		return 'https://cissee.de';
	}

	public function customModuleSupportUrl(): string {
		return 'https://cissee.de';
	}
	
	public function title(): string {
		return $this->vesta.' '.I18N::translate('Vesta Common');
	}
	
	public function description(): string {
		return I18N::translate('A module providing common classes for other \'Vesta\' custom modules. Does not have to be activated.');
	}	
}
