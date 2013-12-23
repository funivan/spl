<?php
  /**
   * @author Ivan Shcherbak <dev@funivan.com>
   */

  namespace Tests\ObjectCollection;

  use Demo\ItemsObjectCollection;
  use Demo\TestObject;

  class ObjectCollectionTest extends \Tests\Main {

    public function testInit() {

      $collection = new ItemsObjectCollection();
      $firstItem = $collection->getFirst();

      $this->assertEmpty($firstItem);

      $error = null;
      try {
        new ItemsObjectCollection(
          array(new \stdClass())
        );
      } catch (\Exception $error) {
      }
      $this->assertInstanceOf('Exception', $error);

      $collection = new ItemsObjectCollection(
        array(new TestObject())
      );

      $this->assertInstanceOf(TestObject::N, $collection->getFirst());

    }
  }