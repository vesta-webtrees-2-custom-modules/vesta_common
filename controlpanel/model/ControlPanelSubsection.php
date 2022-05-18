<?php

namespace Vesta\ControlPanelUtils\Model;

class ControlPanelSubsection {

    private $label;
    private $elements;
    private $description;
    private $header;

    /**
     * 
     * @return string
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * @return ControlPanelElement[]
     */
    public function getElements() {
        return $this->elements;
    }

    /**
     * @return string|null
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getHeader() {
        return $this->header;
    }

    public function __construct($label, $elements, $description = null, $header = null) {
        $this->label = $label;
        $this->elements = $elements;
        $this->description = $description;
        $this->header = $header;
    }

}
