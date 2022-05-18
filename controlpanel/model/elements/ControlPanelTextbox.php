<?php

namespace Vesta\ControlPanelUtils\Model;

class ControlPanelTextbox implements ControlPanelElement {

    private $label;
    private $description;
    private $settingKey;
    private $settingDefaultValue;
    private $required;
    private $maxLength;
    private $pattern;

    public function getLabel() {
        return $this->label;
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

    public function getRequired() {
        return $this->required;
    }

    public function getMaxLength() {
        return $this->maxLength;
    }

    public function getPattern() {
        return $this->pattern;
    }

    /**
     * 
     * @param string $label
     * @param string|null $description
     * @param string $settingKey
     * @param int $settingDefaultValue
     */
    public function __construct(
        $label,
        $description,
        $settingKey,
        $settingDefaultValue,
        $required = true,
        $maxLength = 31,
        $pattern = '[^&lt;&gt;&quot;*?{}():/\\$%|]*') {

        $this->label = $label;
        $this->description = $description;

        $this->settingKey = $settingKey;
        $this->settingDefaultValue = $settingDefaultValue;
        $this->required = $required;
        $this->maxLength = $maxLength;
        $this->pattern = $pattern;
    }

}
