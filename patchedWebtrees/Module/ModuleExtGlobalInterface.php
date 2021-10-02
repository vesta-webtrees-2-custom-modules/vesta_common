<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Module;

use Fisharebest\Webtrees\Module\ModuleGlobalInterface;

/**
 * Interface ModuleExtGlobalInterface - Add global content to the page layout.
 */
interface ModuleExtGlobalInterface extends ModuleGlobalInterface
{
    /**
     * Raw content, to be added at the end of the <body> element.
     * Typically, this will be <script> elements.
     *
     * @return string
     */
    public function bodyContentOnAdminPage(): string;

    /**
     * Raw content, to be added at the end of the <head> element.
     * Typically, this will be <link> and <meta> elements.
     *
     * @return string
     */
    public function headContentOnAdminPage(): string;
}
