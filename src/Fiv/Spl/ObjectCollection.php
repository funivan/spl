<?php

  /**
   * @author Ivan Shcherbak <dev@funivan.com>
   */
  namespace Fiv\Spl;

  /**
   *
   * @package Fiv\Spl
   */
  abstract class ObjectCollection extends Collection {

    public function __clone() {
      $items = array();
      foreach ($this->items as $item) {
        $items[] = clone $item;
      }
      $this->setItems($items);
    }


    /**
     * Used for validation
     * Return class name
     *
     * @codeCoverageIgnore
     * @return string
     */
    public abstract function objectsClassName();


    /**
     * @inheritdoc
     */
    public function prepend($item) {
      $this->validateObject($item, 'You can prepend only object');
      return parent::prepend($item);
    }

    /**
     * @inheritdoc
     */
    public function append($item) {
      $this->validateObject($item, 'You can append only object');
      return parent::append($item);
    }

    /**
     * @inheritdoc
     */
    public function addAfter($index, $items) {
      $this->validateObjects($items, 'You can add after only objects.');
      return parent::addAfter($index, $items);
    }


    /**
     * @inheritdoc
     */
    public function setItems($items) {

      $this->validateObjects($items, 'You can set only array of objects.');

      return parent::setItems($items);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $item) {

      $this->validateObject($item, 'You can set only object to this collections');

      return parent::offsetSet($offset, $item);
    }


    /**
     * Check array of objects.
     *
     * @param array $items
     * @param string $message
     * @throws \Exception
     */
    protected function validateObjects(array $items, $message) {
      $itemClass = $this->objectsClassName();
      foreach ($items as $item) {
        if (($item instanceof $itemClass) == false) {
          throw new \Exception($message . ' (' . $itemClass . ')');
        }
      }
    }

    /**
     * You can add or append only one type of object to collection
     *
     * @param $item
     * @param $message
     * @throws \Exception
     */
    protected function validateObject($item, $message) {
      $itemClass = $this->objectsClassName();
      if (($item instanceof $itemClass) == false) {
        throw new \Exception($message . ' (' . $this->objectsClassName() . ')');
      }
    }

  }

