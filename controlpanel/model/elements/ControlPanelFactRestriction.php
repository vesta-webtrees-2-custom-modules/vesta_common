<?php

namespace Vesta\ControlPanelUtils\Model;

class ControlPanelFactRestriction implements ControlPanelElement {

    private $options;
    private $description;
    private $settingKey;
    private $settingDefaultValue;

    public function getOptions() {
        return $this->options;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getSettingKey() {
        return $this->settingKey;
    }

    public function getSettingDefaultValue() {
        return $this->settingDefaultValue;
    }

    /**
     *
     * @param array $options
     * @param string|null $description
     * @param string $settingKey
     * @param string $settingDefaultValue
     */
    public function __construct($options, $description, $settingKey, $settingDefaultValue) {
        $this->options = $options;
        $this->description = $description;

        $this->settingKey = $settingKey;
        $this->settingDefaultValue = $settingDefaultValue;
    }

    public static function createWithIndividualFacts($description, $settingKey, $settingDefaultValue) {
        return new ControlPanelFactRestriction(PicklistFacts::getPicklistFactsINDI(), $description, $settingKey, $settingDefaultValue);
    }

    public static function createWithFamilyFacts($description, $settingKey, $settingDefaultValue) {
        return new ControlPanelFactRestriction(PicklistFacts::getPicklistFactsFAM(), $description, $settingKey, $settingDefaultValue);
    }

    public static function createWithFacts($options, $description, $settingKey, $settingDefaultValue) {
        return new ControlPanelFactRestriction($options, $description, $settingKey, $settingDefaultValue);
    }

}
