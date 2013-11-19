<?php

  namespace Fiv\Spl;

    //
    //  $string = 'Test-string_other';
    //
    //  # 1
    //  $new = Fiv\Spl\String::init($string);
    //  $new->regexMap('![-|_].!', function ($value) {
    //    return substr(strtoupper($value), 1);
    //  });
    //
    //  # 2
    //  $new = $string;
    //  preg_match_all('![-|_](.)!', $string, $match);
    //  foreach ($match[1] as $name) {
    //    $new = str_replace('-' . $name, '' . ucfirst($name), $partialFilePath);
  //  }

  class String {

    /**
     * @var sting
     */
    protected $string = null;

    public function __construct($string) {
      $this->string = (string)$string;
    }

    public function __toString() {
      return (string)$this->string;
    }

    /**
     * <code>
     * \Fiv\Spl\String::init('tel +388')->regexDel('![^\d]!');
     * </code>
     * @param $string
     * @return Fiv_Spl_String
     */
    public static function init($string) {
      return new String($string);
    }

    /**
     * Remove matched occurrence from string
     * You can pass array or string
     *
     * <code>
     * $string->regexDel('![^a-z]!');
     *
     * $string->regexDel(array('!test\d+!', '\d+new'));
     * </code>
     * @param array|string $regex
     * @return $this
     */
    public function regexDel($regex) {
      $this->regexReplace($regex, '');
      return $this;
    }

    /**
     * Make replacement in string.
     * Arguments combination:
     * string - string
     * array
     * array - string
     * array - array
     *
     * <code>
     * $string->regexReplace('!test([^\s]+)\s!', 'other $1');
     *
     * $string->regexReplace(['!demo[a-z0-9]+!', '\s{2,}']);
     * </code>
     * @param $from
     * @param $to
     * @return $this
     */
    public function regexReplace($from, $to = null) {
      if (is_array($from) and $to === null) {
        foreach ($from as $regexFrom => $regexTo) {
          $this->string = preg_replace($regexFrom, $regexTo, $this->string);
        }

      } else {
        $this->string = preg_replace($from, $to, $this->string);
      }
      return $this;
    }

    /**
     * @param $regex
     * @return int
     */
    public function regexContain($regex) {
      if (!is_array($regex)) {
        $regex = array($regex);
      }
      foreach ($regex as $containRegex) {
        if (preg_match($containRegex, $this->string)) {
          return true;
        }
      }

      return false;
    }

    /**
     * @param string $regex
     * @param null   $callback
     * @return Fiv_Spl_String
     * @throws Exception
     */
    public function regexMap($regex, $callback) {
      if (!is_callable($callback)) {
        throw new \Exception('Invalid callback function');
      }

      preg_match_all($regex, $this->string, $matches);
      if (!empty($matches[0])) {
        $matchesSimple = array();

        foreach ($matches as $index => $items) {
          foreach ($items as $itemIndex => $value) {
            $matchesSimple[$itemIndex][$index] = $value;
          }

        }

        foreach ($matchesSimple as $values) {
          $this->string = str_replace($values[0], $callback($values[0], $values), $this->string);
        }

      }

      return $this;
    }

    /**
     * @param array|string $regex
     * @return $this
     */
    public function strDel($regex) {
      $this->strReplace($regex, '');
      return $this;
    }

    /**
     * @param string|array      $from
     * @param null|string|array $to
     * @return Fiv_Spl_String
     */
    public function strReplace($from, $to = null) {
      if (is_array($from) and $to === null) {
        foreach ($from as $strFrom => $strTo) {
          $this->string = str_replace($strFrom, $strTo, $this->string);
        }
      } else {
        $this->string = str_replace($from, $to, $this->string);
      }

      return $this;
    }

    /**
     * @param  string  $explodeSymbol
     * @param callback $callback
     * @throws Exception
     * @return Fiv_Spl_String
     */
    public function explodeMap($explodeSymbol, $callback) {
      if (!is_callable($callback)) {
        throw new \Exception('Invalid callback function');
      }

      $chunks = explode($explodeSymbol, $this->string);
      foreach ($chunks as $index => $value) {
        $chunks[$index] = $callback($value);
      }

      $this->string = implode($explodeSymbol, $chunks);

      return $this;
    }

  }