<?php

namespace Vesta\Model;

use Fisharebest\Webtrees\Tree;

class LocReference {

    private $xref;
    private $tree;
    private $trace;
    private $level;

    public function getXref(): string {
        return $this->xref;
    }

    public function getTree(): Tree {
        return $this->tree;
    }

    public function getTrace(): Trace {
        return $this->trace;
    }

    public function getLevel(): int {
        return $this->level;
    }

    /**
     * 
     * @param string $xref _LOC identifier
     * @param string $tree
     * @param string $trace
     */
    public function __construct(
        string $xref, 
        Tree $tree, 
        Trace $trace, 
        int $level = 0) {
        
        $this->xref = $xref;
        $this->tree = $tree;
        $this->trace = $trace;
        $this->level = $level;
    }

}
