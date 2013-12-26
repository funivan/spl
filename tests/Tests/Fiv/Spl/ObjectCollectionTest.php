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

    /**
     * @return ItemsObjectCollection
     */
    protected function getTestCollection() {
      $collection = new ItemsObjectCollection();
      $this->assertCount(0, $collection);

      for ($i = 0; $i <= 5; $i++) {
        $item = new TestObject();
        $item->i = $i;
        $collection->append($item);
      }
      return $collection;
    }

    public function testNextAndPev() {

      $collection = $this->getTestCollection();

      $this->assertCount(6, $collection);

      foreach ($collection->iterate() as $i => $token) {
        if ($i === 2) {
          $prev = $collection->getPrevious();
          $this->assertEquals(1, $prev->i);

          $prev = $collection->getPrevious(2);
          $this->assertEquals(0, $prev->i);

          $prev = $collection->getPrevious(10);
          $this->assertEquals(null, $prev);

          $next = $collection->getNext();
          $this->assertEquals(3, $next->i);

          $next = $collection->getNext(3);
          $this->assertEquals(5, $next->i);

          $next = $collection->getNext(10);
          $this->assertEquals(null, $next);

        }
      }

    }

    public function testExtractItems() {

      $collection = $this->getTestCollection();

      $this->assertCount(6, $collection);
      $this->assertEquals(0, $collection->getFirst()->i);

      $newCollection = $collection->extractItems(0, -1);
      $this->assertCount(5, $newCollection);

      $newCollection = $collection->extractItems(0, -1);
      $this->assertEquals(0, $newCollection->getFirst()->i);

      $collection->getFirst()->i = 2;

      $this->assertEquals(2, $newCollection->getFirst()->i);

    }

    public function testRefresh() {
      $collection = $this->getTestCollection();

      $this->assertEquals(0, $collection->key());

      $collection->next();
      $collection->next();
      $this->assertEquals(2, $collection->key());

      $collection->refresh();
      $this->assertEquals(0, $collection->key());

    }

    public function testSetItems() {
      $collection = $this->getTestCollection();

      $error = null;
      try {
        $collection[10] = new \stdClass();
      } catch (\Exception $error) {

      }

      $this->assertInstanceOf('Exception', $error);

      $this->assertCount(6, $collection);

      $collection[10] = new TestObject();
      $this->assertCount(7, $collection);
    }

  }