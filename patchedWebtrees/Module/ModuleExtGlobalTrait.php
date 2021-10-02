<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Module;

/**
 * Trait ModuleExtGlobalTrait - default implementation of ModuleExtGlobalInterface
 */
trait ModuleExtGlobalTrait
{
    /**
     * Raw content, to be added at the end of the <body> element.
     * Typically, this will be <script> elements.
     *
     * @return string
     */
    public function bodyContent(): string
    {
        return '';
    }

    /**
     * Raw content, to be added at the end of the <body> element.
     * Typically, this will be <script> elements.
     *
     * @return string
     */
    public function bodyContentOnAdminPage(): string
    {
        return '';
    } 
    
    /**
     * Raw content, to be added at the end of the <head> element.
     * Typically, this will be <link> and <meta> elements.
     *
     * @return string
     */
    public function headContent(): string
    {
        return '';
    }
    
    /**
     * Raw content, to be added at the end of the <head> element.
     * Typically, this will be <link> and <meta> elements.
     *
     * @return string
     */
    public function headContentOnAdminPage(): string
    {
        return '';
    }  
}
