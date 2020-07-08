<?php

namespace Cissee\WebtreesExt\Http\Controllers;

//abstraction of Place/PlaceLocation functionality
interface PlaceWithinHierarchyBase {
  
  ////////////////////////////////////////////////////////////////////////////////
  //Place
  
  public function url(): string;
  
  public function gedcomName(): string;
    
  public function parent(): PlaceWithinHierarchyBase;  
    
  //for views
  public function placeName(): string;

}
