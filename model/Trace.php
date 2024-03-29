<?php

namespace Vesta\Model;

//TODO: make immutable! but not that add() is in use!
class Trace {

    private $elements;

    public function getAll(): string {
        return implode("; ", $this->elements);
    }

    public function add(string $element): void {
        $this->elements[] = $element;
    }

    public function __construct(string $element) {
        $this->elements = [$element];
    }

}
