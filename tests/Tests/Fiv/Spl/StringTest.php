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

    public function testRegexReplace() {
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

    public function testStrReplace() {
      $string = new String('test-123');
      $string->strReplace('-1', '-1-');
      $this->assertEquals('test-1-23', (string)$string);

      $string = new String('test-124');
      $string->strReplace(array(
        't' => 'a',
        'e' => 'b',
        's' => 'c',
      ));
      $this->assertEquals('abca-124', (string)$string);

    }

    public function testMap() {
      $string = new String('123-120+4');
      $error = null;
      try {
        $string->regexMap('!\d+!', 'ivanlid_callable_function');
      } catch (\Exception $error) {
      }
      $this->assertInstanceOf('Exception', $error);

      $string->regexMap('!\d+!', function ($value) {
        $value .= '.';
        return $value;
      });

      $this->assertEquals('123.-120.+4.', (string)$string);

      $string = new String('abc');
      $string->regexMap('![a-z]!', function ($value) {
        $value .= '.';
        return $value;
      });

      $this->assertEquals('a.b.c.', (string)$string);

    }

    public function testExplodeMap() {
      $string = new String('a.b.cd');
      $error = null;
      try {
        $string->explodeMap('/', 'ivanlid_callable_function');
      } catch (\Exception $error) {
      }
      $this->assertInstanceOf('Exception', $error);

      $string->explodeMap('.', function ($value) {
        $value .= '-new';
        return $value;
      });

      $this->assertEquals('a-new.b-new.cd-new', (string)$string);
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

    public function testStrDelete() {
      $string = new String('test-123');
      $string->strDel('test');
      $this->assertEquals('-123', (string)$string);

      $string = new String('test-123');
      $string->strDel(array('test', '-'));
      $this->assertEquals('123', (string)$string);
    }

  }