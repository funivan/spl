<?php

  namespace Tests\FObjectCollection;

  use Demo\ItemsObjectCollection;
  use Demo\TestObject;

  /**
   * @author Ivan Shcherbak <dev@funivan.com>
   */
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

    public function testClone() {

      $collection = new ItemsObjectCollection();
      $item = new TestObject();
      $item->title = 'test';
      $collection->setItems([$item]);

      $newCollection = clone $collection;

      $newItem = $newCollection->getFirst();
      $newItem->title = 'other';

      $this->assertEquals('other', $newItem->title);
      $this->assertEquals('test', $item->title);

    }

    public function testCount() {

      $collection = new ItemsObjectCollection();
      $this->assertCount(0, $collection);

      $collection->setItems([new TestObject()]);
      $this->assertCount(1, $collection);

      $collection->setItems([new TestObject(), new TestObject()]);
      $this->assertCount(2, $collection);

    }

    public function testSlice() {

      $collection = new ItemsObjectCollection();
      $this->assertCount(0, $collection);

      for ($i = 0; $i < 5; $i++) {
        $item = new TestObject();
        $item->i = $i;
        $collection->append($item);
      }

      $this->assertCount(5, $collection);

      $collection->slice(1);
      $this->assertCount(4, $collection);

      $collection->slice(2, -1);
      $this->assertCount(1, $collection);

      $lastItem = $collection->getLast();

      $this->assertEquals(3, $lastItem->i);
    }
  }