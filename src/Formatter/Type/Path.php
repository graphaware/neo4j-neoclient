<?php

/*
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client\Formatter\Type;

use GraphAware\Common\Type\Node as NodeInterface;
use GraphAware\Common\Type\Path as PathInterface;
use GraphAware\Common\Type\Relationship as RelationshipInterface;

class Path implements PathInterface
{
    /**
     * @var Node[]
     */
    protected $nodes;

    /**
     * @var Relationship[]
     */
    protected $relationships;

    /**
     * @param Node[]         $nodes
     * @param Relationship[] $relationships
     */
    public function __construct(array $nodes, array $relationships)
    {
        $this->nodes = $nodes;
        $this->relationships = $relationships;
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        return $this->nodes[0];
    }

    /**
     * {@inheritdoc}
     */
    public function end()
    {
        return $this->nodes[count($this->nodes) - 1];
    }

    /**
     * {@inheritdoc}
     */
    public function length()
    {
        return count($this->relationships);
    }

    /**
     * {@inheritdoc}
     */
    public function containsNode(NodeInterface $node)
    {
        foreach ($this->nodes as $n) {
            if ($n->identity() === $node->identity()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function containsRelationship(RelationshipInterface $relationship)
    {
        foreach ($this->relationships as $rel) {
            if ($rel->identity() === $relationship->identity()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function nodes()
    {
        return $this->nodes;
    }

    /**
     * {@inheritdoc}
     */
    public function relationships()
    {
        return $this->relationships;
    }
}
