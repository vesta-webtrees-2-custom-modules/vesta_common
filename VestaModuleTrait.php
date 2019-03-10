<?php

namespace Vesta;

use Cissee\WebtreesExt\ModuleView;
use Fisharebest\Webtrees\View;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Vesta\ControlPanel\ControlPanelUtils;
use Vesta\ControlPanel\Model\ControlPanelPreferences;
use Illuminate\Database\Capsule\Manager as DB;

trait VestaModuleTrait {
	
	protected function getVestaSymbol() {
		return json_decode('"\u26B6"');
	}
	
	protected abstract function getMainTitle();
	
  public function getTabTitle($mainTabTitle) {
		$prefix = '';
		$vesta_show = $this->getPreference('VESTA_TAB', '1');
		if ($vesta_show) {
			$prefix = $this->getVestaSymbol().' ';
		}
		return $prefix.$mainTabTitle;
	}
  
	public function getChartTitle($mainChartTitle) {
		$prefix = '';
		$vesta_show = $this->getPreference('VESTA_CHART', '1');
		if ($vesta_show) {
			$prefix = $this->getVestaSymbol().' ';
		}
		return $prefix.$mainChartTitle;
	}
  
	public function getListTitle($mainListTitle) {
		$prefix = '';
		$vesta_show = $this->getPreference('VESTA_LIST', '1');
		if ($vesta_show) {
			$prefix = $this->getVestaSymbol().' ';
		}
		return $prefix.$mainListTitle;
	}
  
	public function title(): string {
		$prefix = '';
		$vesta_show = $this->getPreference('VESTA', '1');
		if ($vesta_show) {
			$prefix = $this->getVestaSymbol().' ';
		}
		return $prefix.$this->getMainTitle();
	}
	
	public function getConfigLink(): string {
		return route('module', [
			'module' => $this->name(),
			'action' => 'Admin',
		]);
	}
	
	public function getAdminAction(): Response {

		//fancy way, example from StoriesModule.php
		/*
		$this->layout = 'layouts/administration';
		return $this->viewResponse('modules/stories/config', [
			'stories'    => $stories,
			'title'      => $this->title() . ' — ' . $tree->title(),
			'tree'       => $tree,
			'tree_names' => Tree::getNameList(),
		]);
		*/
		
		//plain way
		return new Response($this->editConfig(), Response::HTTP_OK);
	}
	
	/**
	 * 
	 * @param $request
	 * @return RedirectResponse
	 */
	public function postAdminAction(Request $request): RedirectResponse
	{
		$this->saveConfig($request);
		
		$url = route('module', [
			'module' => $this->name(),
			'action' => 'Admin',
		]);
		
		return new RedirectResponse($url);
	}
	
	/**
	 * @return ControlPanelPreferences
	 */
	protected abstract function createPrefs();
	
	/**
	 * @return string[]
	 */
	protected abstract function getFullDescription();
	
	/**
	 * return html (form to edit configuration settings)
	 */
	protected function editConfig() {
		ob_start();
		$this->editConfigAfterFaq();
		$editConfigAfterFaq = ob_get_clean();
		
		ob_start();
		$this->editConfigBeforeFaq();
		$editConfigBeforeFaq = ob_get_clean();
		
		ob_start();
		$utils = new ControlPanelUtils($this);
		$utils->printPrefs($this->createPrefs(), $this->name());
		$prefs = ob_get_clean();

		// Render the view (__DIR__ is the directory of VestaModuleTrait - that's intentional: it's a generic view!)
		$innerHtml = ModuleView::make(__DIR__, 'vesta_config', [
					'title' => $this->title(),
					'vesta' => $this->getVestaSymbol(),
					'fullDescription' => $this->getFullDescription(),
					'editConfigBeforeFaq' => $editConfigBeforeFaq,
					'editConfigAfterFaq' => $editConfigAfterFaq,
					'prefs' => $prefs
				]);
						
		// Insert the view into the (main) layout
    $html = View::make('layouts/administration', [
					'title' => $this->title(),
					'content' => $innerHtml
				]);
		
		return $html;
	}
  
	//OverrideHook
	/**
	 * echoes html
	 */
	protected function editConfigBeforeFaq() {}
  
	//OverrideHook
	/**
	 * echoes html
	 */
	protected function editConfigAfterFaq() {}
	
	/**
	 * Save updated configuration settings.
	 */
	protected function saveConfig(Request $request) {
		$utils = new ControlPanelUtils($this);
		$utils->savePostData($request, $this->createPrefs());
	}
	
	//same as Database::getSchema, but use module settings instead of site settings (Issue #3 in personal_facts_with_hooks)
	protected function updateSchema($namespace, $schema_name, $target_version): bool
	{
			try {
					$current_version = (int)$this->getPreference($schema_name);
			} catch (PDOException $ex) {
					// During initial installation, the site_preference table won’t exist.
					$current_version = 0;
			}

			$updates_applied = false;

			// Update the schema, one version at a time.
			while ($current_version < $target_version) {
					$class = $namespace . '\\Migration' . $current_version;
					/** @var MigrationInterface $migration */
					$migration = new $class();
					$migration->upgrade();
					$current_version++;
					
					//when a module is first installed, we may not be able to setPreference at this point
					////(if this is called e.g. from SetName())
					//because of foreign key constraints: 
					//the module may not have been inserted in the 'module' table at this point!
					//cf. ModuleService.all()
					//
					//not that critical, we can just set the preference next time
					//
					//let's just check this directly (using ModuleService at this point may lead to looping, if we're indirectly called from there)
					if (DB::table('module')->where('module_name','=', $this->name())->exists()) {
						$this->setPreference($schema_name, (string) $current_version);
					}					
					$updates_applied = true;
			}

			return $updates_applied;
	}
}
