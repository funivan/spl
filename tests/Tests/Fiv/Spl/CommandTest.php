<?php

  namespace Tests\Fiv\Spl;

  use Demo\CustomCommand;

  class CommandTest extends \Tests\Main {

    public function testRunDir() {
      $command = new CustomCommand();
      $this->assertEquals('/tmp/Demo_CustomCommand', $command->getRunDir());
    }
  }