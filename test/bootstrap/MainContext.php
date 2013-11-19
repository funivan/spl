<?php


  /**
   * You can implement custom steps here
   *
   * @author Ivan Shcherbak <dev@funivan.com>
   */

  namespace Test;

  require_once __DIR__ . '/../../vendor/autoload.php';

  class MainContext extends \Behat\Behat\Context\BehatContext {

    /**
     *
     */
    public function __construct() {
      $this->useContext('StringContext', new StringContext());
    }

  }