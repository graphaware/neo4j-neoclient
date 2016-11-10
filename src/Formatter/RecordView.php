<?php

/*
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client\Formatter;

use GraphAware\Common\Result\RecordViewInterface;
use GraphAware\Common\Type\Node;
use GraphAware\Common\Type\Path;
use GraphAware\Common\Type\Relationship;

class RecordView implements RecordViewInterface
{
    /**
     * @var array
     */
    protected $keys = [];

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var array
     */
    private $keyToIndexMap = [];

    /**
     * @param array $keys
     * @param array $values
     */
    public function __construct(array $keys, array $values)
    {
        $this->keys = $keys;
        $this->values = $values;

        foreach ($this->keys as $i => $k) {
            $this->keyToIndexMap[$k] = $i;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function keys()
    {
        return $this->keys;
    }

    /**
     * {@inheritdoc}
     */
    public function hasValues()
    {
        return !empty($this->values);
    }

    /**
     * @param string $key
     *
     * @return \GraphAware\Neo4j\Client\Formatter\Type\Node|\GraphAware\Neo4j\Client\Formatter\Type\Relationship
     */
    public function value($key)
    {
        return $this->values[$this->keyToIndexMap[$key]];
    }

    /**
     * Returns the Node for value <code>$key</code>. Ease IDE integration.
     *
     * @param string $key
     *
     * @throws \InvalidArgumentException When the value is not null or instance of Node
     *
     * @return \GraphAware\Neo4j\Client\Formatter\Type\Node
     */
    public function nodeValue($key)
    {
        if (!$this->hasValue($key) || !$this->value($key) instanceof Node) {
            throw new \InvalidArgumentException(sprintf('value for %s is not of type %s', $key, Node::class));
        }

        return $this->value($key);
    }

    /**
     * @param string $key
     *
     * @throws \InvalidArgumentException When the value is not null or instance of Relationship
     *
     * @return \GraphAware\Neo4j\Client\Formatter\Type\Relationship
     */
    public function relationshipValue($key)
    {
        if (!$this->hasValue($key) || !$this->value($key) instanceof Relationship) {
            throw new \InvalidArgumentException(sprintf('value for %s is not of type %s', $key, Relationship::class));
        }

        return $this->value($key);
    }

    /**
     * {@inheritdoc}
     */
    public function pathValue($key)
    {
        if (!$this->hasValue($key) || !$this->value($key) instanceof Path) {
            throw new \InvalidArgumentException(sprintf('value for %s is not of type %s', $key, Path::class));
        }

        return $this->value($key);
    }

    /**
     * {@inheritdoc}
     */
    public function values()
    {
        return $this->values;
    }

    /**
     * {@inheritdoc}
     */
    public function hasValue($key)
    {
        return array_key_exists($key, $this->keyToIndexMap);
    }

    /**
     * {@inheritdoc}
     */
    public function valueByIndex($index)
    {
        return $this->values[$index];
    }

    /**
     * @return RecordView
     */
    public function record()
    {
        return clone $this;
    }

    /**
     * @param string $key
     * @param mixed  $defaultValue
     *
     * @return \GraphAware\Neo4j\Client\Formatter\Type\Node|\GraphAware\Neo4j\Client\Formatter\Type\Relationship|mixed
     */
    public function get($key, $defaultValue = null)
    {
        if (!isset($this->keyToIndexMap[$key]) && 2 === func_num_args()) {
            return $defaultValue;
        }

        return $this->value($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getByIndex($index)
    {
        return $this->valueByIndex($index);
    }
}
