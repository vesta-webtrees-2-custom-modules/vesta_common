<?php

namespace Vesta\Model;

use Exception;
use Fisharebest\Webtrees\View;
use function view;

class GenericViewElement {

    private $main;
    private $script;

    /**
     * 
     * @return string html string
     */
    public function getMain(): string {
        return $this->main;
    }

    /**
     * 
     * @return string html string (i.e. script tags must be included)
     */
    public function getScript(): string {
        return $this->script;
    }

    public function __construct(string $main, string $script) {
        $this->main = $main;
        $this->script = $script;
    }

    public static function createEmpty(): GenericViewElement {
        return new GenericViewElement('', '');
    }

    public static function create($main): GenericViewElement {
        return new GenericViewElement($main, '');
    }

    /**
     * 
     * @param GenericViewElement[] $elements
     * @return GenericViewElement
     */
    public static function implode($elements): GenericViewElement {
        $main = '';
        $script = '';
        foreach ($elements as $element) {
          $main .= $element->getMain();
          $script .= $element->getScript();
        }
        return new GenericViewElement($main, $script);
    }

    public static function fromView(
            string $name, 
            array $data = []): GenericViewElement {
      
        //preserve stacks
        $styles = View::stack('styles');
        $javascript = View::stack('javascript');
        
        //render the view
        $gveMain = view($name, $data);
        $gveStyles = View::stack('styles');
        $gveScript = View::stack('javascript');
        
        if ($gveStyles !== '') {
            throw new Exception("styles not supported in GenericViewElement!");
        }
        
        //restore stacks
        View::push('styles');
        echo $styles;
        View::endpush();
        
        View::push('javascript');
        echo $javascript;
        View::endpush();
        
        return new GenericViewElement($gveMain, $gveScript); 
    }
}
