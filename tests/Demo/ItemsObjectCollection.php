<?php

  namespace Demo;

  /**
   * @author Ivan Shcherbak <dev@funivan.com>
   */
  class ItemsObjectCollection extends \Fiv\Spl\ObjectCollection {

    /**
     * Used for validation
     *     PHP
     * @return string
     */
    public function objectsClassName() {
      return TestObject::N;
    }
  }