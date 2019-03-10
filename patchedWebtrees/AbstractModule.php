<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Tree;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Collection;
use stdClass;
use Symfony\Component\HttpFoundation\Response;
use Fisharebest\Webtrees\Module\ModuleInterface;

//same as Fisharebest\Webtrees\Module\AbstractModule, but methods made non-final
/**
 * Class AbstractModule - common functions for blocks
 */
abstract class AbstractModule implements ModuleInterface
{
    /** @var string A unique internal name for this module (based on the installation folder). */
    private $name = '';

    /** @var int The default access level for this module.  It can be changed in the control panel. */
    protected $access_level = Auth::PRIV_PRIVATE;

    /** @var bool The default status for this module.  It can be changed in the control panel. */
    private $enabled = true;

    /** @var string For custom modules - optional (recommended) version number */
    public const CUSTOM_VERSION = '';

    /** @var string For custom modules - link for support, upgrades, etc. */
    public const CUSTOM_WEBSITE = '';

    /** @var string How to render view responses */
    protected $layout = 'layouts/default';

    /**
     * How should this module be labelled on tabs, menus, etc.?
     *
     * @return string
     */
    public function title(): string
    {
        return 'Module name goes here';
    }

    /**
     * A sentence describing what this module does.
     *
     * @return string
     */
    public function description(): string
    {
        return $this->title();
    }

    /**
     * Get a block setting.
     *
     * Originally, this was just used for the home-page blocks.  Now, it is used by any
     * module that has repeated blocks of content on the same page.
     *
     * @param int    $block_id
     * @param string $setting_name
     * @param string $default
     *
     * @return string
     */
    protected function getBlockSetting(int $block_id, string $setting_name, string $default = ''): string
    {
        $settings = app('cache.array')->rememberForever('block_setting' . $block_id, function () use ($block_id): array {
            return DB::table('block_setting')
                ->where('block_id', '=', $block_id)
                ->pluck('setting_value', 'setting_name')
                ->all();
        });

        return $settings[$setting_name] ?? $default;
    }

    /**
     * Set a block setting.
     *
     * @param int    $block_id
     * @param string $setting_name
     * @param string $setting_value
     *
     * @return $this
     */
    protected function setBlockSetting(int $block_id, string $setting_name, string $setting_value): self
    {
        DB::table('block_setting')->updateOrInsert([
            'block_id'      => $block_id,
            'setting_name'  => $setting_name,
        ], [
            'setting_value' => $setting_value,
        ]);

        return $this;
    }

    /**
     * A unique internal name for this module (based on the installation folder).
     *
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * A unique internal name for this module (based on the installation folder).
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Modules are either enabled or disabled.
     *
     * @param bool $enabled
     *
     * @return ModuleInterface
     */
    public function setEnabled(bool $enabled): ModuleInterface
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Modules are either enabled or disabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
    
    /**
     * Should this module be enabled when it is first installed?
     *
     * @return bool
     */
    public function isEnabledByDefault(): bool
    {
        return true;
    }
    
    /**
     * Get a module setting. Return a default if the setting is not set.
     *
     * @param string $setting_name
     * @param string $default
     *
     * @return string
     */
    public function getPreference(string $setting_name, string $default = ''): string
    {
        return DB::table('module_setting')
            ->where('module_name', '=', $this->name())
            ->where('setting_name', '=', $setting_name)
            ->value('setting_value') ?? $default;
    }

    /**
     * Set a module setting.
     *
     * Since module settings are NOT NULL, setting a value to NULL will cause
     * it to be deleted.
     *
     * @param string $setting_name
     * @param string $setting_value
     *
     * @return $this
     */
    public function setPreference(string $setting_name, string $setting_value): void
    {
        DB::table('module_setting')->updateOrInsert([
            'module_name'  => $this->name(),
            'setting_name' => $setting_name,
        ], [
            'setting_value' => $setting_value,
        ]);
    }

    /**
     * Get a the current access level for a module
     *
     * @param Tree   $tree
     * @param string $interface
     *
     * @return int
     */
    public function accessLevel(Tree $tree, string $interface): int
    {
        $access_levels = app('cache.array')
            ->rememberForever('module_privacy' . $tree->id(), function () use ($tree): Collection {
                return DB::table('module_privacy')
                    ->where('gedcom_id', '=', $tree->id())
                    ->get();
            });

        $row = $access_levels->filter(function (stdClass $row) use ($interface): bool {
            return $row->interface === $interface && $row->module_name === $this->name();
        })->first();

        return $row ? (int) $row->access_level : $this->access_level;
    }

    /**
     * Create a response object from a view.
     *
     * @param string  $view_name
     * @param mixed[] $view_data
     * @param int     $status
     *
     * @return Response
     */
    protected function viewResponse($view_name, $view_data, $status = Response::HTTP_OK): Response
    {
        // Make the view's data available to the layout.
        $layout_data = $view_data;

        // Render the view
        $layout_data['content'] = view($view_name, $view_data);

        // Insert the view into the layout
        $html = view($this->layout, $layout_data);

        return new Response($html, $status);
    }
}
