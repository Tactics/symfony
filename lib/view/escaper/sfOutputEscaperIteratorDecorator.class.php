<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Output escaping iterator decorator.
 *
 * This takes an object that implements the Traversable interface and turns it
 * into an iterator with each value escaped.
 *
 * Note: Prior to PHP 5.1, the IteratorIterator class was not implemented in the
 * core of PHP. This means that although it will still work with classes that
 * implement Iterator or IteratorAggregate, internal PHP classes that only
 * implement the Traversable interface will cause the constructor to throw an
 * exception.
 *
 * @see        sfOutputEscaper
 *
 * @author     Mike Squire <mike@somosis.co.uk>
 *
 * @version    SVN: $Id: sfOutputEscaperIteratorDecorator.class.php 3232 2007-01-11 20:51:54Z fabien $
 */
class sfOutputEscaperIteratorDecorator extends sfOutputEscaperGetterDecorator implements Iterator, Countable, ArrayAccess, Stringable
{
    /**
     * The iterator to be used.
     *
     * @var IteratorIterator
     */
    private $iterator;

    /**
     * Constructs a new escaping iteratoror using the escaping method and value supplied.
     *
     * @param string The escaping method to use
     * @param Traversable The iterator to escape
     */
    public function __construct($escapingMethod, Traversable $value)
    {
        // Set the original value for __call(). Set our own iterator because passing
        // it to IteratorIterator will lose any other method calls.

        parent::__construct($escapingMethod, $value);

        $this->iterator = new IteratorIterator($value);
    }

    /**
     * Resets the iterator (as required by the Iterator interface).
     *
     * @return bool true, if the iterator rewinds successfully otherwise false
     */
    public function rewind(): void
    {
        $this->iterator->rewind();
    }

    /**
     * Escapes and gets the current element (as required by the Iterator interface).
     *
     * @return mixed The escaped value
     */
    public function current(): mixed
    {
        return sfOutputEscaper::escape($this->escapingMethod, $this->iterator->current());
    }

    /**
     * Gets the current key (as required by the Iterator interface).
     *
     * @return string Iterator key
     */
    public function key(): mixed
    {
        return $this->iterator->key();
    }

    /**
     * Moves to the next element in the iterator (as required by the Iterator interface).
     */
    public function next(): void
    {
        $this->iterator->next();
    }

    /**
     * Returns whether the current element is valid or not (as required by the
     * Iterator interface).
     *
     * @return bool true if the current element is valid; false otherwise
     */
    public function valid(): bool
    {
        return $this->iterator->valid();
    }

    /**
     * Returns true if the supplied offset is set in the array (as required by
     * the ArrayAccess interface).
     *
     * @param string The offset of the value to check existance of
     *
     * @return bool true if the offset exists; false otherwise
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->value);
    }

    /**
     * Returns the element associated with the offset supplied (as required by the ArrayAccess interface).
     *
     * @param string The offset of the value to get
     *
     * @return mixed The escaped value
     */
    public function offsetGet($offset): mixed
    {
        return sfOutputEscaper::escape($this->escapingMethod, $this->value[$offset]);
    }

    /**
     * Throws an exception saying that values cannot be set (this method is
     * required for the ArrayAccess interface).
     *
     * This (and the other sfOutputEscaper classes) are designed to be read only
     * so this is an illegal operation.
     *
     * @param string (ignored)
     * @param string (ignored)
     *
     * @throws <b>sfException</b>
     */
    public function offsetSet($offset, $value): void
    {
        throw new sfException('Cannot set values.');
    }

    /**
     * Throws an exception saying that values cannot be unset (this method is
     * required for the ArrayAccess interface).
     *
     * This (and the other sfOutputEscaper classes) are designed to be read only
     * so this is an illegal operation.
     *
     * @param string (ignored)
     *
     * @throws <b>sfException</b>
     */
    public function offsetUnset($offset): void
    {
        throw new sfException('Cannot unset values.');
    }

    /**
     * Returns the size of the array (are required by the Countable interface).
     *
     * @return int The size of the array
     */
    public function count(): int
    {
        return count($this->value);
    }

    /**
     * Returns the result of calling the get() method on the object, bypassing
     * any escaping, if that method exists.
     *
     * If there is not a callable get() method this will throw an exception.
     *
     * @param string The parameter to be passed to the get() get method
     *
     * @return mixed The unescaped value returned
     *
     * @throws <b>sfException</b> if the object does not have a callable get() method
     */
    public function getRaw($key): mixed
    {
        if (!is_callable([$this->value, 'get'])) {
            throw new sfException('Object does not have a callable get() method.');
        }

        return $this->value->get($key);
    }

    /**
     * Magic PHP method that intercepts method calls, calls them on the objects
     * that is being escaped and escapes the result.
     *
     * The calling of the method is changed slightly to accommodate passing a
     * specific escaping strategy. An additional parameter is appended to the
     * argument list which is the escaping strategy. The decorator will remove
     * and use this parameter as the escaping strategy if it begins with 'esc_'
     * (the prefix all escaping helper functions have).
     *
     * For example if an object, $o, implements methods a() and b($arg):
     *
     *   $o->a()                // Escapes the return value of a()
     *   $o->a(ESC_RAW)         // Uses the escaping method ESC_RAW with a()
     *   $o->b('a')             // Escapes the return value of b('a')
     *   $o->b('a', ESC_RAW);   // Uses the escaping method ESC_RAW with b('a')
     *
     * @param string The method on the object to be called
     * @param array An array of arguments to be passed to the method
     *
     * @return mixed The escaped value returned by the method
     */
    public function __call($method, $args)
    {
        if (count($args) > 0) {
            $escapingMethod = $args[count($args) - 1];
            if (is_string($escapingMethod) && str_starts_with($escapingMethod, 'esc_')) {
                array_pop($args);
            } else {
                $escapingMethod = $this->escapingMethod;
            }
        } else {
            $escapingMethod = $this->escapingMethod;
        }

        $value = call_user_func_array([$this->value, $method], $args);

        return sfOutputEscaper::escape($escapingMethod, $value);
    }

    /**
     * Try to call decorated object __toString() method if exists.
     */
    public function __toString(): string
    {
        return (string) $this->escape($this->escapingMethod, $this->value->__toString());
    }
}
