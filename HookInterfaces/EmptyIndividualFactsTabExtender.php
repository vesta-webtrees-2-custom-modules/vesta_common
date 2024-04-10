<?php

namespace Vesta\Hook\HookInterfaces;

use Fisharebest\Webtrees\Fact;
use Fisharebest\Webtrees\GedcomRecord;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Tree;
use Vesta\Model\GenericViewElement;
use Vesta\Model\PlaceStructure;

/**
 * base impl of IndividualFactsTabExtenderInterface
 */
trait EmptyIndividualFactsTabExtender {

    protected $factsTabUIElementOrder = 0;

    public function setFactsTabUIElementOrder(int $order): void {
        $this->factsTabUIElementOrder = $order;
    }

    public function getFactsTabUIElementOrder(): int {
        return $this->factsTabUIElementOrder ?? $this->defaultFactsTabUIElementOrder();
    }

    public function defaultFactsTabUIElementOrder(): int {
        return 9999;
    }

    public function hFactsTabRequiresModalVesta(Tree $tree): ?string {
        return null;
    }

    public function hFactsTabGetAdditionalFacts(GedcomRecord $record) {
        return array();
    }

    public function hFactsTabGetStyleadds(
        GedcomRecord $record,
        Fact $fact): array {

        return array();
    }

    public function hFactsTabGetOutputBeforeTab(
        GedcomRecord $record): GenericViewElement {

        return new GenericViewElement('', '');
    }

    public function hFactsTabGetOutputAfterTab(
        GedcomRecord $record,
        bool $ajax): GenericViewElement {

        return new GenericViewElement('', '');
    }

    public function hFactsTabGetOutputInDBox(
        GedcomRecord $record): GenericViewElement {

        return new GenericViewElement('', '');
    }

    public function hFactsTabGetOutputAfterDBox(
        Individual $person): GenericViewElement {

        return new GenericViewElement('', '');
    }

    public function factPlaceAdditionsBeforePlace(PlaceStructure $place): ?string {
        return null;
    }

    public function factPlaceAdditionsAfterMap(PlaceStructure $place): ?string {
        return null;
    }

    public function factPlaceAdditionsAfterNotes(PlaceStructure $place): ?string {
        return null;
    }

    //public function factPlaceAdditions(PlaceStructure $place): ?FactPlaceAdditions {
    //  return null;
    //}

    public function hFactsTabGetOutputForAssoRel(
        Fact $event,
        Individual $person,
        Individual $associate,
        $relationship_prefix,
        $relationship_name,
        $relationship_suffix,
        $inverse) {

        //do not return null - null has special semantics ('abort')
        return new GenericViewElement('', '');
    }

    public function hFactsTabGetAdditionalEditControls(
        Fact $event): GenericViewElement {

        return new GenericViewElement('', '');
    }

}
