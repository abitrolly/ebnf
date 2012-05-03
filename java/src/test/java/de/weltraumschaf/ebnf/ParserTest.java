package de.weltraumschaf.ebnf;

import de.weltraumschaf.ebnf.ast.Notification;
import static de.weltraumschaf.ebnf.ast.builder.SyntaxBuilder.syntax;
import de.weltraumschaf.ebnf.ast.nodes.Syntax;
import de.weltraumschaf.ebnf.util.ReaderHelper;
import java.io.BufferedReader;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.net.URISyntaxException;
import java.net.URL;
import java.util.Arrays;
import static org.junit.Assert.*;
import org.junit.Ignore;
import org.junit.Test;

/**
 * Unit test for Parser.
 *
 * @author Sven Strittmatter <weltraumschaf@googlemail.com>
 */
public class ParserTest {

    private BufferedReader loadFixture(String fixtureFile) throws FileNotFoundException, URISyntaxException {
        URL resource = getClass().getResource("/de/weltraumschaf/ebnf/" + fixtureFile);
        return ReaderHelper.createFrom(resource.toURI());
    }

    private void assertEquivalentSyntax(Syntax expected, Syntax actual) {
        Notification n = new Notification();
        expected.probeEquivalence(actual, n);
        assertNotificationOk(n);
        n = new Notification();
        actual.probeEquivalence(expected, n);
        assertNotificationOk(n);
    }

    private void assertNotificationOk(Notification n) {
        assertTrue(n.report(), n.isOk());
    }

    @Test public void testAssertToken() {
        Parser p  = new Parser(new Scanner(ReaderHelper.createFrom("")));
        Token t1 = new Token(TokenType.ASIGN, ":", new Position(0, 0));
        Token t2 = new Token(TokenType.IDENTIFIER, "foobar", new Position(0, 0));

        assertTrue(p.assertToken(t1, TokenType.ASIGN, ":"));
        assertFalse(p.assertToken(t1, TokenType.IDENTIFIER, ":"));
        assertFalse(p.assertToken(t1, TokenType.ASIGN, ","));
        assertFalse(p.assertToken(t1, TokenType.IDENTIFIER, ","));

        assertTrue(p.assertToken(t2, TokenType.IDENTIFIER, "foobar"));
        assertFalse(p.assertToken(t2, TokenType.ASIGN, "foobar"));
        assertFalse(p.assertToken(t2, TokenType.IDENTIFIER, "snafu"));
        assertFalse(p.assertToken(t2, TokenType.ASIGN, "snafu"));
    }

    @Test public void testAssertTokens() {
        Parser p = new Parser(new Scanner(ReaderHelper.createFrom("")));
        Token t1 = new Token(TokenType.ASIGN, ":", new Position(0, 0));
        Token t2 = new Token(TokenType.ASIGN, "=", new Position(0, 0));

        assertTrue(p.assertTokens(t1, TokenType.ASIGN, Arrays.asList("=", ":", ":==")));
        assertFalse(p.assertTokens(t1, TokenType.ASIGN, Arrays.asList("+", "-", "*")));
        assertTrue(p.assertTokens(t2, TokenType.ASIGN, Arrays.asList("=", ":", ":==")));
        assertFalse(p.assertTokens(t2, TokenType.ASIGN, Arrays.asList("+", "-", "*")));
    }

    @Ignore("TODO: Implement range parsing.")
    @Test public void testParseRanges() throws FileNotFoundException, URISyntaxException {
//        Parser p = new Parser(new Scanner(loadFixture("rules_with_ranges.ebnf")));
//        assertXmlStringEqualsXmlFile(
//            EBNF_TESTS_FIXTURS . DIRECTORY_SEPARATOR . "rules_with_ranges.xml",
//            p.parse().saveXML(),
//            "rules with ranges"
//        );
    }

    @Test public void testParse() throws SyntaxError, FileNotFoundException, IOException, URISyntaxException {
        Syntax ast;

        Parser p = new Parser(new Scanner(loadFixture("rules_with_different_assignment_ops.ebnf")));
        ast = syntax("Rules with different assignment operators.")
            .rule("comment1")
                .identifier("literal1")
            .end()
            .rule("comment2")
                .identifier("literal2")
            .end()
            .rule("comment3")
                .identifier("literal3")
            .end()
        .build();
        assertEquivalentSyntax(ast, p.parse());

        p = new Parser(new Scanner(loadFixture("rules_with_literals.ebnf")));
        ast = syntax("Rules with literal.")
            .rule("literal")
                .choice()
                    .sequence()
                        .terminal("'")
                        .identifier("character")
                        .loop()
                            .identifier("character")
                        .end()
                        .terminal("'")
                    .end()
                    .sequence()
                        .terminal("\"")
                        .identifier("character")
                        .loop()
                            .identifier("character")
                        .end()
                        .terminal("\"")
                    .end()
                .end()
            .end()
        .build();
        assertEquivalentSyntax(ast, p.parse());

        p = new Parser(new Scanner(loadFixture("rules_with_comments.ebnf")));
        ast = syntax("Rules with comments.")
            .comment("(* here are rules *)")
            .rule("title")
                .identifier("literal")
            .end()
            .comment("(* Comment * at the end of line *)")
            .rule("comment")
                .identifier("literal")
            .end()
            .comment("(* This is a multi\n       line comment. *)")
            .rule("comment")
                .comment("(* foo *)")
                .sequence()
                    .comment("(* bar *)")
                    .identifier("literal")
                    .comment("(* baz *)")
                .end()
            .end()
        .build();
        assertEquivalentSyntax(ast, p.parse());

        p = new Parser(new Scanner(loadFixture("testgrammar_1.old.ebnf")));
        ast = syntax("EBNF defined in itself.")
            .rule("syntax")
                .sequence()
                    .option()
                        .identifier("title")
                    .end()
                    .terminal("{")
                    .loop()
                        .identifier("rule")
                    .end()
                    .terminal("}")
                    .option()
                        .identifier("comment")
                    .end()
                .end()
            .end()
            .rule("rule")
                .sequence()
                    .identifier("identifier")
                    .choice()
                        .terminal("=")
                        .terminal(":")
                        .terminal(":==")
                    .end()
                    .identifier("expression")
                    .choice()
                        .terminal(".")
                        .terminal(";")
                    .end()
                .end()
            .end()
            .rule("expression")
                .sequence()
                    .identifier("term")
                    .loop()
                        .sequence()
                            .terminal("|")
                            .identifier("term")
                        .end()
                    .end()
                .end()
            .end()
            .rule("term")
                .sequence()
                    .identifier("factor")
                    .loop()
                        .identifier("factor")
                    .end()
                .end()
            .end()
            .rule("factor")
                .choice()
                    .identifier("identifier")
                    .identifier("literal")
                    .identifier("range")
                    .sequence()
                        .terminal("[")
                        .identifier("expression")
                        .terminal("]")
                    .end()
                    .sequence()
                        .terminal("(")
                        .identifier("expression")
                        .terminal(")")
                    .end()
                    .sequence()
                        .terminal("{")
                        .identifier("expression")
                        .terminal("}")
                    .end()
                .end()
            .end()
            .rule("identifier")
                .sequence()
                    .identifier("character")
                    .loop()
                        .identifier("character")
                    .end()
                .end()
            .end()
            .rule("range")
                .sequence()
                    .identifier("character")
                    .terminal("..")
                    .identifier("character")
                .end()
            .end()
            .rule("title")
                .identifier("literal")
            .end()
            .rule("comment")
                .identifier("literal")
            .end()
            .rule("literal")
                .choice()
                    .sequence()
                        .terminal("'")
                        .identifier("character")
                        .loop()
                            .identifier("character")
                        .end()
                        .terminal("'")
                    .end()
                    .sequence()
                        .terminal("\"")
                        .identifier("character")
                        .loop()
                            .identifier("character")
                        .end()
                        .terminal("\"")
                    .end()
                .end()
            .end()
            .rule("character")
                .choice()
                    .terminal("a")
                    .terminal("b")
                    .terminal("c")
                    .terminal("d")
                    .terminal("e")
                    .terminal("f")
                    .terminal("g")
                    .terminal("h")
                    .terminal("i")
                    .terminal("j")
                    .terminal("k")
                    .terminal("l")
                    .terminal("m")
                    .terminal("n")
                    .terminal("o")
                    .terminal("p")
                    .terminal("q")
                    .terminal("r")
                    .terminal("s")
                    .terminal("t")
                    .terminal("u")
                    .terminal("v")
                    .terminal("w")
                    .terminal("x")
                    .terminal("y")
                    .terminal("z")
                    .terminal("A")
                    .terminal("B")
                    .terminal("C")
                    .terminal("D")
                    .terminal("E")
                    .terminal("F")
                    .terminal("G")
                    .terminal("H")
                    .terminal("I")
                    .terminal("J")
                    .terminal("K")
                    .terminal("L")
                    .terminal("M")
                    .terminal("N")
                    .terminal("O")
                    .terminal("P")
                    .terminal("Q")
                    .terminal("R")
                    .terminal("S")
                    .terminal("T")
                    .terminal("U")
                    .terminal("V")
                    .terminal("W")
                    .terminal("X")
                    .terminal("Y")
                    .terminal("Z")
                .end()
            .end()
        .build();
        assertEquivalentSyntax(ast, p.parse());
    }

    @Ignore("TODO: Implement test with errornous syntax fixtures.")
    @Test public void testParseErrors() {

    }
}
