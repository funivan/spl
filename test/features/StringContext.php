<?php

  namespace Test;

  /**
   * Features context.
   */
  class StringContext extends \Behat\Behat\Context\BehatContext {

    /**
     * @var \Fiv\Spl\String
     */
    protected $string = null;

    /**
     * @Given /^i have string "([^"]*)"$/
     */
    public function iHaveString($string) {
      $this->string = new \Fiv\Spl\String($string);
    }

    /**
     * @When /^I run regexp delete "([^"]*)"$/
     */
    public function iRunRegexpDelete($arg1) {
      $this->string->regexDel($arg1);
    }

    /**
     * @When /^I run regex replace from "([^"]*)" to "([^"]*)"$/
     */
    public function iRunRegexpReplace($from, $to) {
      $this->string->regexReplace($from, $to);
    }


    /**
     * @Then /^I expect "([^"]*)"$/
     */
    public function iExpect($arg1) {
      if ((string)$this->string !== $arg1) {
        throw new \Exception('String invalid. Expect: ' . $arg1 . 'Current: ' . $this->string);
      }
    }

  }
