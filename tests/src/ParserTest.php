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
 * @package tests
 */

namespace de\weltraumschaf\ebnf;

/**
 * @see Parser
 */
require_once 'Parser.php';
/**
 * @see Scanner
 */
require_once 'Scanner.php';
/**
 * @see Token
 */
require_once 'Token.php';

/**
 * Expose protected methods of  {@link Parser} for testing.
 *
 * @package tests
 */
class ExposedParser extends Parser {
    public function exposedAssertToken(Token $token, $type, $value) {
        return parent::assertToken($token, $type, $value);
    }

    public function exposedAssertTokens(Token $token, $type, array $values) {
        return parent::assertTokens($token, $type, $values);
    }
}

/**
 * Testcase for class {@link Parser}.
 *
 * @package tests
 */
class ParserTest extends \PHPUnit_Framework_TestCase {

    private function loadFixture($file) {
        return file_get_contents(EBNF_TESTS_FIXTURS . DIRECTORY_SEPARATOR . $file);
    }

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

    public function testParse() {
        $p = new Parser(new Scanner($this->loadFixture("rules_with_different_assignment_ops.ebnf")));
        $this->assertXmlStringEqualsXmlFile(
            EBNF_TESTS_FIXTURS . DIRECTORY_SEPARATOR . "rules_with_different_assignment_ops.xml",
            $p->parse()->saveXML(),
            "rules with different assignment ops"
        );

        $p = new Parser(new Scanner($this->loadFixture("rules_with_literals.ebnf")));
        $this->assertXmlStringEqualsXmlFile(
            EBNF_TESTS_FIXTURS . DIRECTORY_SEPARATOR . "rules_with_literals.xml",
            $p->parse()->saveXML(),
            "rules with literals"
        );

        $p = new Parser(new Scanner($this->loadFixture("testgrammar_1.old.ebnf")));
        $this->assertXmlStringEqualsXmlFile(
            EBNF_TESTS_FIXTURS . DIRECTORY_SEPARATOR . "testgrammar_1.xml",
            $p->parse()->saveXML(),
            "testgrammar 1"
        );

        $this->markTestIncomplete();
        $p = new Parser(new Scanner($this->loadFixture("rules_with_ranges.ebnf")));
        $this->assertXmlStringEqualsXmlFile(
            EBNF_TESTS_FIXTURS . DIRECTORY_SEPARATOR . "rules_with_ranges.xml",
            $p->parse()->saveXML(),
            "rules with ranges"
        );
    }

    public function testParseAst() {
        $p = new Parser(new Scanner($this->loadFixture("rules_with_different_assignment_ops.ebnf")));
        $p->parse();
        $ast = $p->getAst();
        $this->assertEquals("Rules with different assignment operators.", $ast->title);
        $this->assertEquals(Parser::DEFAULT_META, $ast->meta);
        $this->assertTrue($ast->hasChildren());
        $this->assertEquals(3, $ast->countChildren());
        $rules = $ast->getIterator();

        foreach ($rules as $rule) {
            $this->assertInstanceOf("de\weltraumschaf\ebnf\ast\Rule", $rule);
            $this->assertEquals("comment", $rule->name);
        }

        $this->markTestIncomplete();
    }

    private function assertEquivalentSyntax(Syntax $expected, Syntax $actual) {
        $this->assertNotificationOk($expected->probeEquivalence($actual));
        $this->assertNotificationOk($actual->probeEquivalence($expected));
    }

    private function assertNotificationOk(Notification $n) {
        $this->assertTrue($n->isOk(), $n->report());
    }
}
