<?php


  namespace Demo;

  use Fiv\Spl\Command;

  /**
   * @author Ivan Shcherbak <dev@funivan.com>
   */
  class CustomCommand extends Command {

    protected function run() {

    }

    public function getRunDir() {
      return parent::getRunDir();
    }

  }