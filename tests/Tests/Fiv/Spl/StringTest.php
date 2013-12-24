<?php

  namespace Tests\Fiv\Spl;

  use Fiv\Spl\String;

  /**
   * @package Tests\String
   */
  class StringTest extends \Tests\Main {

    public function testInit() {
      $string = String::init('test-123');
      $this->assertEquals('test-123', (string)$string);

    }

    public function testDelete() {
      $string = new String('test-123');
      $string->regexDel('!\d!');
      $this->assertEquals('test-', (string)$string);
    }

    public function testReplace() {
      $string = new String('test-123');
      $string->regexReplace('!-\d+!', '-test');
      $this->assertEquals('test-test', (string)$string);
    }

  }