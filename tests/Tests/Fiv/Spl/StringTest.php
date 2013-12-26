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

      $string = new String('test-124');
      $string->regexReplace(array(
        '![a-z]+!' => '_',
        '![\d]+!' => '/',
      ));
      $this->assertEquals('_-/', (string)$string);
    }

    public function testRegexContain() {
      $string = new String('test-123');
      $result = $string->regexContain('!-\d+!');
      $this->assertTrue($result);

      $result = $string->regexContain('!f\d!');
      $this->assertFalse($result);

      $string = new String('test-124');
      $result = $string->regexContain(array(
        '![a-z]+!',
        '![\d]+!',
      ));
      $this->assertTrue($result);
      $result = $string->regexContain(array(
        '!md\d!',
        '!cc[a-z]+!',
      ));
      $this->assertFalse($result);
    }

  }