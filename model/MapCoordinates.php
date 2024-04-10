<?php

namespace Vesta\Model;

class MapCoordinates {

    private $lati;
    private $long;
    private $trace;

    public function getLati(): string {
        return $this->lati;
    }

    public function getLong(): string {
        return $this->long;
    }

    public function getTrace(): Trace {
        return $this->trace;
    }

    /**
     *
     * @param string $lati format: -5.6789
     * @param string $long format: -5.6789
     * @param string $trace
     */
    public function __construct(
        string $lati,
        string $long,
        Trace $trace) {

        $this->lati = $lati;
        $this->long = $long;
        $this->trace = $trace;
    }

}
