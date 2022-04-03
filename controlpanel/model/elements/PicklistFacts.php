<?php

namespace Vesta\ControlPanelUtils\Model;

use Fisharebest\Webtrees\Contracts\ElementInterface;
use Fisharebest\Webtrees\Elements\UnknownElement;
use Fisharebest\Webtrees\GedcomTag;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Webtrees;
use Illuminate\Support\Collection;
use function str_starts_with;

class PicklistFacts {
    
    /**
     * Get a list of facts, for use in the "fact picker" edit control
     *
     * @param string $fact_type
     *
     * @return array<string>
     */
    public static function getPicklistFactsINDI(): array {
        
      if (str_starts_with(Webtrees::VERSION, '2.1')) {
          
        //cf TreePreferencesPage
        //should we really exclude NOTE, SOUR, and OBJE here?
          
        $ignore_facts = ['CHAN', 'CHIL', 'FAMC', 'FAMS', 'HUSB', 'NOTE', 'OBJE', 'SOUR', 'SUBM', 'WIFE'];

        $all_individual_facts = Collection::make(Registry::elementFactory()->make('INDI')->subtags())
            ->filter(static fn (string $value, string $key): bool => !in_array($key, $ignore_facts, true))
            ->mapWithKeys(static fn (string $value, string $key): array => [$key => 'INDI:' . $key])
            ->map(static fn (string $tag): ElementInterface => Registry::elementFactory()->make($tag))
            ->filter(static fn (ElementInterface $element): bool => !$element instanceof UnknownElement)
            ->map(static fn (ElementInterface $element): string => $element->label())
            ->sort(I18N::comparator());
        
        return $all_individual_facts->all();
      }
      
      return GedcomTag::getPicklistFacts('INDI');
    }
    
    /**
     * Get a list of facts, for use in the "fact picker" edit control
     *
     * @param string $fact_type
     *
     * @return array<string>
     */
    public static function getPicklistFactsFAM(): array {
        
      if (str_starts_with(Webtrees::VERSION, '2.1')) {
          
        //cf TreePreferencesPage
        //should we really exclude NOTE, SOUR, and OBJE here?
           
        $ignore_facts = ['CHAN', 'CHIL', 'FAMC', 'FAMS', 'HUSB', 'NOTE', 'OBJE', 'SOUR', 'SUBM', 'WIFE'];

        $all_family_facts = Collection::make(Registry::elementFactory()->make('FAM')->subtags())
            ->filter(static fn (string $value, string $key): bool => !in_array($key, $ignore_facts, true))
            ->mapWithKeys(static fn (string $value, string $key): array => [$key => 'FAM:' . $key])
            ->map(static fn (string $tag): ElementInterface => Registry::elementFactory()->make($tag))
            ->filter(static fn (ElementInterface $element): bool => !$element instanceof UnknownElement)
            ->map(static fn (ElementInterface $element): string => $element->label())
            ->sort(I18N::comparator());
        
        return $all_family_facts->all();
      }
      
      return GedcomTag::getPicklistFacts('FAM');
    }
}
