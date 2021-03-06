<?php

/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @license http://www.gnu.org/licenses/ GNU General Public License
 * @author  Sven Strittmatter <ich@weltraumschaf.de>
 * @package ast
 */

namespace de\weltraumschaf\ebnf\ast;

/**
 * @see Composite
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Composite.php';
/**
 * @see DepthCalculator
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'DepthCalculator.php';
/**
 * @see Node
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Node.php';
/**
 * @see Type
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Type.php';

use \ArrayIterator;
use de\weltraumschaf\ebnf\visitor\Visitor;

/**
 * Syntax node.
 *
 * The root of the AST.
 *
 * @package ast
 * @version @@version@@
 */
class Syntax implements Node, Composite {

    const DEFAULT_META = "xis/ebnf v2.0 http://wiki.karmin.ch/ebnf/ gpl3";

    /**
     * Title literal of string.
     *
     * @var string
     */
    public $title = "";
    /**
     * Meta literal of string.
     *
     * @var string
     */
    public $meta = self::DEFAULT_META;

    /**
     * Holds the child nodes.
     *
     * @var array
     */
    private $nodes;

    /**
     * Initializes root node with 0 child nodes.
     */
    public function __construct() {
        $this->nodes = array();
    }

    /**
     * Returns the name of a node.
     *
     * @return string
     */
    public function getNodeName() {
        return Type::SYNTAX;
    }

    /**
     * Implements IteratorAggregate for retrieving an interaotr.
     *
     * @return ArrayIterator
     */
    public function getIterator() {
        return new ArrayIterator($this->nodes);
    }

    /**
     * Count of direct children nodes.
     *
     * @return int
     */
    public function countChildren() {
        return count($this->nodes);
    }

    /**
     * Whether the node has direct child nodes or not.
     *
     * @return bool
     */
    public function hasChildren() {
        return 0 < $this->countChildren();
    }

    /**
     * Append a child {@link Node} to the list of children.
     *
     * @param Node $child Child node to add.
     *
     * @return void
     */
    public function addChild(Node $child) {
        $this->nodes[] = $child;
    }

    /**
     * Probes equivalence of itself against an other node and collects all
     * errors in the passed {@link Notification} object.
     *
     * @param Node         $other  Node to compare against.
     * @param Notification $result Object which collects all equivlanece violations.
     *
     * @return void
     */
    public function probeEquivalence(Node $other, Notification $result) {
        if ( ! $other instanceof Syntax) {
            $result->error(
                "Probed node types mismatch: '%s' != '%s'!",
                get_class($this),
                get_class($other)
            );
            return;
        }

        if ($this->title !== $other->title) {
            $result->error("Titles of syntx differs: '%s' != '%s'!", $this->title, $other->title);
        }

        if ($this->meta !== $other->meta) {
            $result->error("Meta of syntx differs: '%s' != '%s'!", $this->meta, $other->meta);
        }

        /* @var $other Composite */
        if ($this->countChildren() !== $other->countChildren()) {
            $result->error(
                "Node %s has different child count than other: %d != %d!",
                $this->getNodeName(),
                $this->countChildren(),
                $other->countChildren()
            );
        }

        $subnodes      = $this->getIterator();
        $otherSubnodes = $other->getIterator();

        foreach ($subnodes as $subnode) {
            if ($otherSubnodes->offsetExists($subnodes->key())) {
                $subnode->probeEquivalence($otherSubnodes->offsetGet($subnodes->key()), $result);
            } else {
                $result->error("Other node has not the expected subnode!");
            }

        }
    }

    /**
     * Defines method to accept {@link Visitors}.
     *
     * Imlements {@link http://en.wikipedia.org/wiki/Visitor_pattern Visitor Pattern}.
     *
     * @param Visitor $visitor Object which visits te node.
     *
     * @return void
     */
    public function accept(Visitor $visitor) {
        $visitor->beforeVisit($this);
        $visitor->visit($this);

        if ($this->hasChildren()) {
            foreach ($this->getIterator() as $subnode) {
                $subnode->accept($visitor);
            }
        }

        $visitor->afterVisit($this);
    }

    /**
     * Chooses the max depth of its direct childs and returns it plus one.
     *
     * @return int
     */
    public function depth() {
        $calc = new DepthCalculator($this);

        return $calc->depth();
    }
}