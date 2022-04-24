<?php


namespace Vesta\Model;

use Cissee\WebtreesExt\MoreI18N;
use Fisharebest\Webtrees\Contracts\ElementInterface;
use Fisharebest\Webtrees\Elements\UnknownElement;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Registry;
use Illuminate\Support\Collection;

class PlaceHistory {
    
    public static function initialFactsStringForPreferences(): string {
        return 'OCCU,PROP,RESI';
    }
    
    public static function getPicklistFacts(): array {
        
        //opinionated sub-selection
        //we don't consider others to be  useful in place history
        //also, we'd have to support other plural forms for I18N
        //
        //also used for order of checkboxes
        $arr = [
            'BIRT' => 'INDI:BIRT',
            'BAPM' => 'INDI:BAPM',
            'CHR' => 'INDI:CHR',
            'CONF' => 'INDI:CONF',
            
            'RESI' => 'INDI:RESI',
            'OCCU' => 'INDI:OCCU',
            'PROP' => 'INDI:PROP',

            'DEAT' => 'INDI:DEAT',
            'BURI' => 'INDI:BURI',
            
            
            //'MARR' => 'FAM:MARR',
            ];
        
        $facts = Collection::make($arr)
                ->map(static fn(string $tag): ElementInterface => Registry::elementFactory()->make($tag))
                ->filter(static fn(ElementInterface $element): bool => !$element instanceof UnknownElement)
                ->map(static fn(ElementInterface $element): string => $element->label())
                ->sort(I18N::comparator());

        return $facts->all();
    }
    
    public static function getLabel(string $key): string {
        return PlaceHistory::getLabels()[$key];
    }
    
    public static function getLabels(): array {
        return [
            'BIRT' => MoreI18N::xlate('Births'),
            'BAPM' => /* I18N: Plural for Gedcom tag BAPM */ I18N::translate('Baptisms'),
            'CHR' => /* I18N: Plural for Gedcom tag CHR */ I18N::translate('Christenings'),
            'CONF' => /* I18N: Plural for Gedcom tag CONF */ I18N::translate('Confirmations'),
            
            'OCCU' => MoreI18N::xlate('Occupations'),
            'RESI' => /* I18N: Gedcom tag RESI */ I18N::translate('Residences'),
            'PROP' => /* I18N: Plural for Gedcom tag PROP */ I18N::translate('Possessions'),
            
            'DEAT' => MoreI18N::xlate('Deaths'),
            'BURI' => MoreI18N::xlate('Burials'),            
            
            //'MARR' => 'FAM:MARR',
            ];
    }
}
