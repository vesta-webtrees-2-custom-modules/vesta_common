<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt;

use Exception;
use Fisharebest\Webtrees\DebugBar;
use ReflectionClass;
use Throwable;

/**
 * Simple view/template class.
 * adapted from View.php
 * 
 */
class ModuleView 
{
	
    // Where do our templates live
    const TEMPLATE_PATH = 'resources/views/';

    // File extension for our template files.
    const TEMPLATE_EXTENSION = '.phtml';
		
		private $module_directory;
		
    /**
     * @var string The (file) name of the view.
     */
    private $name;

    /**
     * @var mixed[] Data to be inserted into the view.
     */
    private $data;

    /**
     * @var mixed[] Data to be inserted into all views.
     */
    private static $shared_data = [];

    /**
     * @var string Implementation of Blade "stacks".
     */
    private static $stack;

    /**
     * @var array[] Implementation of Blade "stacks".
     */
    private static $stacks = [];

		/**
		 * Create a view from a template name and optional data.
		 *
		 * @param string $module
		 * @param string $name
		 * @param array $data
		 */
		public function __construct(string $module_directory, string $name, $data = []) {
			$this->module_directory = $module_directory;
			$this->name = $name;
			$this->data = $data;
		}
	
		/**
     * Shared data that is available to all views.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public static function share(string $key, $value)
    {
        self::$shared_data[$key] = $value;
    }

    /**
     * Implementation of Blade "stacks".
     *
     * @see https://laravel.com/docs/5.5/blade#stacks
     *
     * @param string $stack
     *
     * @return void
     */
    public static function push(string $stack)
    {
        self::$stack = $stack;
        ob_start();
    }

    /**
     * Implementation of Blade "stacks".
     *
     * @return void
     */
    public static function endpush()
    {
        self::$stacks[self::$stack][] = ob_get_clean();
    }

    /**
     * Implementation of Blade "stacks".
     *
     * @param string $stack
     *
     * @return string
     */
    public static function stack(string $stack): string
    {
        $content = implode('', self::$stacks[$stack] ?? []);

        self::$stacks[$stack] = [];

        return $content;
    }
		
    /**
     * Render a view.
     *
     * @return string
     * @throws Throwable
     */
    public function render(): string
    {
				//also make available any shared data from View (e.g. 'tree' set via index.php)
				$reflectionClass = new ReflectionClass("Fisharebest\Webtrees\View");
				$property = $reflectionClass->getProperty('shared_data');
				$property->setAccessible(true);
				$viewSharedData = $property->getValue();
        
				$variables_for_view = $this->data + self::$shared_data + $viewSharedData;
        extract($variables_for_view);

        try {
            ob_start();
            // Do not use require, so we can catch errors for missing files
            include $this->getFilenameForView($this->module_directory, $this->name);

            return ob_get_clean();
        } catch (Throwable $ex) {
            ob_end_clean();
            throw $ex;
        }
    }
		
    /**
     * Allow a theme to override the default views.
     *
     * @param string $view_name
     *
     * @return string
     * @throws Exception
     */
    public function getFilenameForView($module_directory, $view_name): string
    {
        foreach ($this->paths($module_directory) as $path) {
            $view_file = $path . $view_name . self::TEMPLATE_EXTENSION;

            if (is_file($view_file)) {
                return $view_file;
            }
        }

        throw new Exception('View not found: ' . e($module_directory . ";" . $view_name));
    }

    /**
     * Create and render a view in a single operation.
     *
     * @param string  $name
     * @param mixed[] $data
     *
     * @return string
     */
    public static function make($module_directory, $name, $data = []): string
    {
        $view = new static($module_directory, $name, $data);

        DebugBar::addView($module_directory . ':' . $name, $data);

        return $view->render();
    }

		/**
     * @return string[]
     */
    private function paths($module_directory): array
    {
        /*static*/ $paths = [];

        if (empty($paths)) {
					
						//[RC] adjusted: only use specific module!
						$paths = glob($module_directory . '/' . self::TEMPLATE_PATH);
						//[RC] how is this supposed to work (in View.php) if multiple modules override the same view?
						//we still need something more specific!
						//
            // Module views
            // @TODO - this includes disabled modules.
            //$paths = glob(WT_ROOT . Webtrees::MODULES_PATH . '*/' . self::TEMPLATE_PATH);
            // Theme views
            //$paths[] = WT_ROOT . Webtrees::THEMES_PATH . Theme::theme()->themeId() . self::TEMPLATE_PATH;
            // Core views
            //$paths[] = WT_ROOT . self::TEMPLATE_PATH;
						
            $paths = array_filter($paths, function (string $path): bool {
                return is_dir($path);
            });
        }

        return $paths;
    }
}
