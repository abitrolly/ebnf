package de.weltraumschaf.ebnf;

import de.weltraumschaf.ebnf.parser.EbnfScanner;
import de.weltraumschaf.ebnf.parser.EbnfParser;
import de.weltraumschaf.ebnf.ast.nodes.Syntax;
import de.weltraumschaf.ebnf.parser.SyntaxException;
import de.weltraumschaf.ebnf.util.ReaderHelper;
import de.weltraumschaf.ebnf.visitor.TextSyntaxTree;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.IOException;
import org.apache.commons.cli.HelpFormatter;
import org.apache.commons.cli.ParseException;

/**
 * Main application class.
 *
 * @author Sven Strittmatter <weltraumschaf@googlemail.com>
 */
public final class App {

    private final String[] args;

    private App(final String[] args) {
        this.args = args.clone();
    }

    public static void main(final String[] args) {
        try {
            final App app = new App(args);
            exit(app.run());
        } catch (EbnfException e) {
            println(e.getMessage());
            exit(e.getCode());
        } catch (Exception ex) { //NOPMD
            println("Fatal error!");
            ex.printStackTrace(System.out);
            exit(ExitCode.FATAL_ERROR);
        }
    }

    private static void exit(final ExitCode code) {
        exit(code.getCode());
    }

    private static void exit(final int code) {
        System.exit(code);
    }

    private ExitCode run()  throws EbnfException {
        final CliOptions options = parseOptions();

        if (options.isHelp()) {
            options.format(new HelpFormatter());
            return ExitCode.OK;
        }

        if (!options.hasSyntaxFile()) {
            println("No syntax file given!");
            return ExitCode.NO_SYNTAX;
        }

        EbnfScanner scanner;
        EbnfParser parser;
        Syntax ast;

        try {
            scanner = new EbnfScanner(ReaderHelper.createFrom(new File(options.getSyntaxFile())));
            parser  = new EbnfParser(scanner);
            ast     = parser.parse();
        } catch (SyntaxException ex) {
            println("Syntax error: " + ex.getMessage());

            if (options.isDebug()) {
                ex.printStackTrace(System.out);
            }

            return ExitCode.SYNTAX_ERROR;
        } catch (FileNotFoundException ex) {
            println(String.format("Can not read syntax file '%s'!", options.getSyntaxFile()));

            if (options.isDebug()) {
                ex.printStackTrace(System.out);
            }

            return ExitCode.READ_ERROR;
        } catch (IOException ex) {
            println(String.format("Can not read syntax file '%s'!", options.getSyntaxFile()));

            if (options.isDebug()) {
                ex.printStackTrace(System.out);
            }

            return ExitCode.READ_ERROR;
        }

        if (options.isTextTree()) {
            final TextSyntaxTree visitor = new TextSyntaxTree();
            ast.accept(visitor);
            println(visitor.getText());
        }

        return ExitCode.OK;
    }

    private CliOptions parseOptions() throws EbnfException {
        final CliOptions options = new CliOptions();

        try {
            options.parse(args);
        } catch (ParseException ex) {
            throw new EbnfException(ex.getMessage(), 1, ex);
        }

        return options;
    }

    private static void println(final String str) {
        // CHECKSTYLE:OFF
        System.out.println(str);
        // CHECKSTYLE:ON
    }
}