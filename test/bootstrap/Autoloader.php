<?php
  namespace Test;

  class Autoloader {

    const N = __CLASS__;

    /**
     * Autoload function
     *
     * @author ivan scherbak <dev@funivan.com>
     * @param string $className
     */
    public static function autoload($className) {
      if (strpos($className, __NAMESPACE__) !== false and $shortClassName = substr(ltrim($className, '\\'), strlen(__NAMESPACE__) + 1)) {
        $file = __DIR__
          . DIRECTORY_SEPARATOR
          . '..'
          . DIRECTORY_SEPARATOR
          . 'features'
          . DIRECTORY_SEPARATOR

          . str_replace('\\', DIRECTORY_SEPARATOR, $shortClassName)
          . '.php';

        include_once $file;
      }
    }
  }