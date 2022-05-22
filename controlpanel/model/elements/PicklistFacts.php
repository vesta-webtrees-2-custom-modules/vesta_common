<?php

namespace Vesta\ControlPanelUtils\Model;

use Fisharebest\Webtrees\Contracts\ElementInterface;
use Fisharebest\Webtrees\Elements\UnknownElement;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Registry;
use Illuminate\Support\Collection;

class PicklistFacts {

    /**
     * Get a list of facts, for use in the "fact picker" edit control
     *
     * @param string $fact_type
     *
     * @return array<string>
     */
    public static function getPicklistFactsINDI(): array {

        //cf TreePreferencesPage
        //should we really exclude NOTE, SOUR, and OBJE here?

        $ignore_facts = ['CHAN', 'CHIL', 'FAMC', 'FAMS', 'HUSB', 'NOTE', 'OBJE', 'SOUR', 'SUBM', 'WIFE'];

        $all_individual_facts = Collection::make(Registry::elementFactory()->make('INDI')->subtags())
            ->filter(static fn(string $value, string $key): bool => !in_array($key, $ignore_facts, true))
            ->mapWithKeys(static fn(string $value, string $key): array => [$key => 'INDI:' . $key])
            ->map(static fn(string $tag): ElementInterface => Registry::elementFactory()->make($tag))
            ->filter(static fn(ElementInterface $element): bool => !$element instanceof UnknownElement)
            ->map(static fn(ElementInterface $element): string => $element->label())
            ->sort(I18N::comparator());

        return $all_individual_facts->all();
    }

    /**
     * Get a list of facts, for use in the "fact picker" edit control
     *
     * @param string $fact_type
     *
     * @return array<string>
     */
    public static function getPicklistFactsFAM(): array {

        //cf TreePreferencesPage
        //should we really exclude NOTE, SOUR, and OBJE here?

        $ignore_facts = ['CHAN', 'CHIL', 'FAMC', 'FAMS', 'HUSB', 'NOTE', 'OBJE', 'SOUR', 'SUBM', 'WIFE'];

        $all_family_facts = Collection::make(Registry::elementFactory()->make('FAM')->subtags())
            ->filter(static fn(string $value, string $key): bool => !in_array($key, $ignore_facts, true))
            ->mapWithKeys(static fn(string $value, string $key): array => [$key => 'FAM:' . $key])
            ->map(static fn(string $tag): ElementInterface => Registry::elementFactory()->make($tag))
            ->filter(static fn(ElementInterface $element): bool => !$element instanceof UnknownElement)
            ->map(static fn(ElementInterface $element): string => $element->label())
            ->sort(I18N::comparator());

        return $all_family_facts->all();
    }

}
