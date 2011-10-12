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

namespace de\weltraumschaf\ebnf;

require_once "Parser.php";
require_once "Scanner.php";
require_once "Token.php";

class ExposedParser extends Parser {
    public function exposedAssertToken(Token $token, $type, $value) {
        return parent::assertToken($token, $type, $value);
    }

    public function exposedAssertTokens(Token $token, $type, array $values) {
        return parent::assertTokens($token, $type, $values);
    }
}

/**
 * Testcase for class {Parser}.
 */
class ParserTest extends \PHPUnit_Framework_TestCase {

    public function testAssertToken() {
        $p  = new ExposedParser(new Scanner(""));
        $t1 = new Token(Token::OPERATOR, ":", new Position(0, 0));
        $t2 = new Token(Token::IDENTIFIER, "foobar", new Position(0, 0));

        $this->assertTrue($p->exposedAssertToken($t1, Token::OPERATOR, ":"));
        $this->assertFalse($p->exposedAssertToken($t1, Token::IDENTIFIER, ":"));
        $this->assertFalse($p->exposedAssertToken($t1, Token::OPERATOR, ","));
        $this->assertFalse($p->exposedAssertToken($t1, Token::IDENTIFIER, ","));

        $this->assertTrue($p->exposedAssertToken($t2, Token::IDENTIFIER, "foobar"));
        $this->assertFalse($p->exposedAssertToken($t2, Token::OPERATOR, "foobar"));
        $this->assertFalse($p->exposedAssertToken($t2, Token::IDENTIFIER, "snafu"));
        $this->assertFalse($p->exposedAssertToken($t2, Token::OPERATOR, "snafu"));
    }

    public function testAssertTokens() {
        $p = new ExposedParser(new Scanner(""));
        $t1 = new Token(Token::OPERATOR, ":", new Position(0, 0));
        $t2 = new Token(Token::OPERATOR, "=", new Position(0, 0));

        $this->assertTrue($p->exposedAssertTokens($t1, Token::OPERATOR, array("=", ":", ":==")));
        $this->assertFalse($p->exposedAssertTokens($t1, Token::OPERATOR, array("+", "-", "*")));
        $this->assertTrue($p->exposedAssertTokens($t2, Token::OPERATOR, array("=", ":", ":==")));
        $this->assertFalse($p->exposedAssertTokens($t2, Token::OPERATOR, array("+", "-", "*")));
    }

}