<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Elements;

use Fisharebest\Webtrees\Elements\AbstractElement;
use Fisharebest\Webtrees\Tree;
use function e;
use function rawurlencode;
use function strtoupper;

//webtrees (from 2.0.16) now has its own FamilySearchFamilyTreeId
//linking to a glossy but rather useless 'discovery version' of the page though
class FamilySearchFamilyTreeId extends AbstractElement
{
    protected const EXTERNAL_URL = 'https://www.familysearch.org/tree/person/details/';

    protected const MAXIMUM_LENGTH = 8;

    /**
     * Convert a value to a canonical form.
     *
     * @param string $value
     *
     * @return string
     */
    public function canonical(string $value): string
    {
        return strtoupper(parent::canonical($value));
    }

    /**
     * Display the value of this type of element.
     *
     * @param string $value
     * @param Tree   $tree
     *
     * @return string
     */
    public function value(string $value, Tree $tree): string
    {
        $canonical = $this->canonical($value);
        $url       = static::EXTERNAL_URL . rawurlencode($canonical);

        return '<a dir="ltr" href="' . e($url) . '" rel="nofollow">' . e($canonical) . '</a>';
    }
}
