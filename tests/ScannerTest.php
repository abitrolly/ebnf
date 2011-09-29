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
 * @author Vincent Tscherter <tscherter@karmin.ch>
 * @author Sven Strittmatter <ich@weltraumschaf.de>
 */

namespace Weltraumschaf\Ebnf;

require_once "Scanner.php";

/**
 * Testcase for class Scanner.
 */
class ScannerTest extends \PHPUnit_Framework_TestCase {

    private $ops = array("(", ")", "[", "]", "{", "}", "=", ".");
    private $lowAlpha = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r",
        "s", "t", "u", "v", "w", "x", "y", "z");
    private $upAlpha = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
        "S", "T", "U", "V", "W", "X", "Y", "Z");
    private $nums = array("1", "2", "3", "4", "5",  "6",  "7",  "8",  "9", "0");
    private $ws = array(" ", "\n", "\r", "\t");

    public function testIsAlpha() {
        foreach ($this->lowAlpha as $c) {
            $this->assertTrue(Scanner::isAlpha($c), $c);
        }

        foreach ($this->upAlpha as $c) {
            $this->assertTrue(Scanner::isAlpha($c), $c);
        }

        foreach ($this->nums as $c) {
            $this->assertFalse(Scanner::isAlpha($c), $c);
        }

        foreach ($this->ops as $c) {
            $this->assertFalse(Scanner::isAlpha($c), $c);
        }

        foreach ($this->ws as $c) {
            $this->assertFalse(Scanner::isAlpha($c), $c);
        }
    }

    public function testIsNum() {
        foreach ($this->nums as $c) {
            $this->assertTrue(Scanner::isNum($c), $c);
        }

        foreach ($this->ops as $c) {
            $this->assertFalse(Scanner::isNum($c), $c);
        }

        foreach ($this->lowAlpha as $c) {
            $this->assertFalse(Scanner::isNum($c), $c);
        }

        foreach ($this->upAlpha as $c) {
            $this->assertFalse(Scanner::isNum($c), $c);
        }

        foreach ($this->ws as $c) {
            $this->assertFalse(Scanner::isNum($c), $c);
        }
    }

    public function testIsAlphaNum() {
        foreach ($this->nums as $c) {
            $this->assertTrue(Scanner::isAlphaNum($c), $c);
        }

        foreach ($this->lowAlpha as $c) {
            $this->assertTrue(Scanner::isAlphaNum($c), $c);
        }

        foreach ($this->upAlpha as $c) {
            $this->assertTrue(Scanner::isAlphaNum($c), $c);
        }

        foreach ($this->ops as $c) {
            $this->assertFalse(Scanner::isAlphaNum($c), $c);
        }

        foreach ($this->ws as $c) {
            $this->assertFalse(Scanner::isAlphaNum($c), $c);
        }
    }

    public function testIsOperator() {
        foreach ($this->ops as $c) {
            $this->assertTrue(Scanner::isOperator($c), $c);
        }

        foreach ($this->nums as $c) {
            $this->assertFalse(Scanner::isOperator($c), $c);
        }

        foreach ($this->lowAlpha as $c) {
            $this->assertFalse(Scanner::isOperator($c), $c);
        }

        foreach ($this->upAlpha as $c) {
            $this->assertFalse(Scanner::isOperator($c), $c);
        }

        foreach ($this->ws as $c) {
            $this->assertFalse(Scanner::isOperator($c), $c);
        }
    }

    public function testIsWhiteSpace() {
        foreach ($this->ws as $c) {
            $this->assertTrue(Scanner::isWhiteSpace($c), $c);
        }

        foreach ($this->ops as $c) {
            $this->assertFalse(Scanner::isWhiteSpace($c), $c);
        }

        foreach ($this->nums as $c) {
            $this->assertFalse(Scanner::isWhiteSpace($c), $c);
        }

        foreach ($this->lowAlpha as $c) {
            $this->assertFalse(Scanner::isWhiteSpace($c), $c);
        }

        foreach ($this->upAlpha as $c) {
            $this->assertFalse(Scanner::isWhiteSpace($c), $c);
        }
    }

    public function testIsQuote() {
        $this->assertTrue(Scanner::isQuote('"'));
        $this->assertTrue(Scanner::isQuote("'"));

        foreach ($this->ws as $c) {
            $this->assertFalse(Scanner::isQuote($c), $c);
        }

        foreach ($this->ops as $c) {
            $this->assertFalse(Scanner::isQuote($c), $c);
        }

        foreach ($this->nums as $c) {
            $this->assertFalse(Scanner::isQuote($c), $c);
        }

        foreach ($this->lowAlpha as $c) {
            $this->assertFalse(Scanner::isQuote($c), $c);
        }

        foreach ($this->upAlpha as $c) {
            $this->assertFalse(Scanner::isQuote($c), $c);
        }
    }

    public function testNext() {
        $s = new Scanner("title = literal .");
        $t = $s->next();
        $this->assertInstanceOf("Weltraumschaf\Ebnf\Token", $t);
        $this->assertEquals("title", $t->getValue());
        $this->assertEquals(Token::IDENTIFIER, $t->getType());
        $p = $t->getPosition();
        $this->assertInstanceOf("Weltraumschaf\Ebnf\Position", $p);
        $this->assertNull($p->getFile());
        $this->assertEquals(1, $p->getLine());
        $this->assertEquals(1, $p->getColumn());

        $t = $s->next();
        $this->assertInstanceOf("Weltraumschaf\Ebnf\Token", $t);
        $this->assertEquals("=", $t->getValue());
        $this->assertEquals(Token::OPERATOR, $t->getType());
        $p = $t->getPosition();
        $this->assertInstanceOf("Weltraumschaf\Ebnf\Position", $p);
        $this->assertNull($p->getFile());
        $this->assertEquals(1, $p->getLine());
        $this->assertEquals(7, $p->getColumn());

        $t = $s->next();
        $this->assertInstanceOf("Weltraumschaf\Ebnf\Token", $t);
        $this->assertEquals("literal", $t->getValue());
        $this->assertEquals(Token::IDENTIFIER, $t->getType());
        $p = $t->getPosition();
        $this->assertInstanceOf("Weltraumschaf\Ebnf\Position", $p);
        $this->assertNull($p->getFile());
        $this->assertEquals(1, $p->getLine());
        $this->assertEquals(9, $p->getColumn());

        $t = $s->next();
        $this->assertInstanceOf("Weltraumschaf\Ebnf\Token", $t);
        $this->assertEquals(".", $t->getValue());
        $this->assertEquals(Token::OPERATOR, $t->getType());
        $p = $t->getPosition();
        $this->assertInstanceOf("Weltraumschaf\Ebnf\Position", $p);
        $this->assertNull($p->getFile());
        $this->assertEquals(1, $p->getLine());
        $this->assertEquals(17, $p->getColumn());

        $t = $s->next();
        $this->assertInstanceOf("Weltraumschaf\Ebnf\Token", $t);
        $this->assertEquals("EOF", $t->getValue());
        $this->assertEquals(Token::EOF, $t->getType());
        $p = $t->getPosition();
        $this->assertInstanceOf("Weltraumschaf\Ebnf\Position", $p);
        $this->assertNull($p->getFile());
        $this->assertEquals(1, $p->getLine());
        $this->assertEquals(17, $p->getColumn());
    }
}
