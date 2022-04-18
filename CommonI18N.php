<?php

namespace Vesta;

use Cissee\WebtreesExt\MoreI18N;
use Fisharebest\Webtrees\I18N;

class CommonI18N {

    public static function getVestaSymbol() {
        return json_decode('"\u26B6"');
    }

    //////////////////////////////////////////////////////////////////////////////
    //module titles
    //this module is always enabled, therefore no need to use ModuleI18N for titleVestaXxx(),

    public static function titleVestaCommon(): string {
        return /* I18N: Module title */ I18N::translate('Vesta Common');
    }

    public static function titleVestaCLAF(): string {
        return /* I18N: Module title */ I18N::translate('Vesta Classic Look & Feel');
    }

    public static function titleVestaPersonalFacts(): string {
        return /* I18N: Module title */ I18N::translate('Vesta Facts and events');
    }

    public static function titleVestaRelatives(): string {
        return /* I18N: Module title */ I18N::translate('Vesta Families');
    }

    public static function titleVestaGov4Webtrees(): string {
        return /* I18N: Module title */ I18N::translate('Vesta Gov4Webtrees');
    }

    public static function titleVestaSharedPlaces(): string {
        return /* I18N: Module title */ I18N::translate('Vesta Shared Places');
    }

    public static function titleVestaExtendedRelationships(): string {
        return /* I18N: Module title */ I18N::translate('Vesta Extended Relationships');
    }

    public static function titleVestaResearchSuggestions(): string {
        return /* I18N: Module title */ I18N::translate('Vesta Research Suggestions');
    }

    public static function titleVestaLocationData(): string {
        return /* I18N: Module title */ I18N::translate('Vesta Webtrees Location Data Provider');
    }

    public static function titleVestaPlacesAndPedigreeMap(): string {
        return /* I18N: Module title */ I18N::translate('Vesta Places and Pedigree map');
    }

    public static function titleVestaClippingsCart(): string {
        return /* I18N: Module title */ I18N::translate('Vesta Clippings Cart');
    }

    //////////////////////////////////////////////////////////////////////////////

    public static function requires1(string $title1): string {
        return /* I18N: Module Configuration */I18N::translate(
                'Requires the \'%1$s %2$s\' module.',
                CommonI18N::getVestaSymbol(),
                $title1);
    }

    public static function requires2(string $title1, string $title2): string {
        return /* I18N: Module Configuration */I18N::translate(
                'Requires the \'%1$s %2$s\' module, and the \'%1$s %3$s\' module.',
                CommonI18N::getVestaSymbol(),
                $title1,
                $title2);
    }

    public static function requires3(string $title1, string $title2, string $title3): string {
        return /* I18N: Module Configuration */I18N::translate(
                'Requires the \'%1$s %2$s\' module, the \'%1$s %3$s\' module, and the \'%1$s %4$s\' module.',
                CommonI18N::getVestaSymbol(),
                $title1,
                $title2,
                $title3);
    }

    public static function providesLocationData(): string {
        return /* I18N: Module Configuration */I18N::translate('Provides location data to other custom modules.');
    }

    public static function locationDataProviders(): string {
        return /* I18N: Module Configuration */I18N::translate('Location Data Providers');
    }

    public static function mapCoordinates(): string {
        return /* I18N: Module Configuration */I18N::translate('Modules listed here are used (in the configured order) to determine map coordinates of places.');
    }

    //////////////////////////////////////////////////////////////////////////////

    public static function readme(): string {
        return /* I18N: Module configuration: Refers to the module's Readme file */ I18N::translate('Readme');
    }

    public static function readmeLocationData(): string {
        return /* I18N: Module configuration: Refers to general documentation */ I18N::translate('Vesta location data management overview');
    }

    public static function general(): string {
        return MoreI18N::xlate('General');
    }

    public static function options(): string {
        return /* I18N: Module Configuration */I18N::translate('Options');
    }

    public static function displayedTitle(): string {
        return /* I18N: Module Configuration */I18N::translate('Displayed title');
    }

    public static function displayedData(): string {
        return /* I18N: Module Configuration */I18N::translate('Displayed data');
    }

    public static function vestaSymbolInTabTitle(): string {
        return /* I18N: Module Configuration */I18N::translate('Include the %1$s symbol in the tab title', CommonI18N::getVestaSymbol());
    }

    public static function vestaSymbolInChartTitle(): string {
        return /* I18N: Module Configuration */I18N::translate('Include the %1$s symbol in the chart menu entry title', CommonI18N::getVestaSymbol());
    }

    public static function vestaSymbolInListTitle(): string {
        return /* I18N: Module Configuration */I18N::translate('Include the %1$s symbol in the list menu entry title', CommonI18N::getVestaSymbol());
    }

    public static function vestaSymbolInSidebarTitle(): string {
        return /* I18N: Module Configuration */I18N::translate('Include the %1$s symbol in the sidebar title', CommonI18N::getVestaSymbol());
    }

    public static function vestaSymbolInClippingsCartTitle(): string {
        return /* I18N: Module Configuration */I18N::translate('Include the %1$s symbol in the clippings cart menu entry title', CommonI18N::getVestaSymbol());
    }

    public static function vestaSymbolInTitle2(): string {
        return /* I18N: Module Configuration */I18N::translate('Deselect in order to have the title appear exactly as the original title.');
    }

    //////////////////////////////////////////////////////////////////////////////

    public static function restrictIndi(): string {
        return /* I18N: Module Configuration */I18N::translate('Restrict to this list of GEDCOM individual facts and events. You can modify this list by removing or adding fact and event names, even custom ones, as necessary.');
    }

    public static function restrictFam(): string {
        return /* I18N: Module Configuration */I18N::translate('Restrict to this list of GEDCOM family facts and events. You can modify this list by removing or adding fact and event names, even custom ones, as necessary.');
    }

    public static function bothEmpty(): string {
        return /* I18N: Module Configuration */I18N::translate('In particular if both lists are empty, no additional facts and events of this kind will be shown.');
    }

    //TODO: also used on the family page!
    public static function factsAndEventsTabSettings(): string {
        return /* I18N: Module Configuration */I18N::translate('Facts and Events Tab Settings');
    }

    public static function placeHistory(): string {
        return I18N::translate('Place history');
    }
}
