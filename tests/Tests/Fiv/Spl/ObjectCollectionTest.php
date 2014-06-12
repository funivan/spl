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

      foreach ($collection as $i => $token) {
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

      $collection->rewind();
      $this->assertEquals(0, $collection->key());

    }

    public function testMap() {
      $collection = $this->getTestCollection();
      $collection->map(function ($token, $index, $collectionObject) {
        $token->i++;
        $this->assertTrue(is_integer($index));
        $this->assertTrue(is_object($collectionObject));
      });

      $this->assertEquals(1, $collection->getFirst()->i);

      $error = null;
      try {
        $collection->map('invalid_callback_function_name');
      } catch (\Exception $error) {

      }
      $this->assertInstanceOf('Exception', $error);
    }

    public function testPrepend() {
      $collection = $this->getTestCollection();

      $error = null;
      try {
        $collection->prepend('1');
      } catch (\Exception $error) {
      }
      $this->assertInstanceOf('Exception', $error);

      $this->assertEquals(0, $collection->getFirst()->i);
      $test = new TestObject();
      $test->custom = 1;
      $collection->prepend($test);

      $this->assertEquals(1, $collection->getFirst()->custom);
    }

    public function testAddItems() {
      $collection = $this->getTestCollection();

      $error = null;
      try {
        $collection->addAfter(1, '1');
      } catch (\Exception $error) {
      }
      $this->assertInstanceOf('Exception', $error);

      $error = null;
      try {
        $collection->addAfter('1', array(
          new TestObject()
        ));
      } catch (\Exception $error) {
      }
      $this->assertInstanceOf('Exception', $error);

      $testObject = new TestObject();
      $testObject->custom = true;
      $collection->addAfter(1, array($testObject));

      $this->assertTrue($collection[2]->custom);
    }

    public function testSetRemoveGetItems() {
      $collection = $this->getTestCollection();

      $error = null;
      try {
        $collection[10] = new \stdClass();
      } catch (\Exception $error) {

      }

      $this->assertInstanceOf('Exception', $error);

      $this->assertCount(6, $collection);

      $testObject = new TestObject();
      $collection[10] = $testObject;
      $this->assertCount(7, $collection);
      $this->assertTrue(isset($collection[10]));
      $this->assertEquals($testObject, $collection[10]);
      unset($collection[10]);
      $this->assertCount(6, $collection);

      $collection[] = new TestObject();
      $this->assertCount(7, $collection);

      $this->assertTrue(is_array($collection->getItems()));

    }

  }