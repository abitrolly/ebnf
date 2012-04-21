package de.weltraumschaf.ebnf;

/**
 *
 * @author Sven Strittmatter <weltraumschaf@googlemail.com>
 */
public class Token {

    /**
     * One of the class constants.
     */
    private TokenType type;

    /**
     * The literal string.
     */
    private String value;

    /**
     * Start position in source.
     */
    private Position position;

    /**
     * Initializes the immutable object.
     *
     * @param type     Type of token. One of the class constants.
     * @param value    The scanned token string.
     * @param position The start position of scanned token.
     */
    public Token(TokenType type, String value, Position position) {
        this.type     = type;
        this.value    = value;
        this.position = position;
    }

    /**
     * Returns token type as string.
     *
     * @return
     */
    public TokenType getType() {
        return type;
    }

    /**
     * Returns the scanned token string.
     *
     * @return
     */
    public String getValue() {
        return getValue(false);
    }

    /**
     * Returns the scanned token string.
     *
     * @param unquote Whether to unquote a literal value.
     * @return
     */
    public String getValue(boolean unquote) {
        if (unquote) {
            return unquoteString(value);
        }

        return value;
    }

    /**
     * Returns the start position of the token string in the source.
     *
     * @return
     */
    public Position getPosition() {
        return getPosition(false);
    }

    /**
     * Returns the start position of the token string in the source.
     *
     * @param end If true the tokens end position is returned instead of the start.
     * @return
     */
    public Position getPosition(boolean end) {
        if (end) {
            return new Position(
                position.getLine(),
                position.getColumn() + value.length(),
                position.getFile()
            );
        }

        return position;
    }

    /**
     * Human readable string representation.
     *
     * Token values longer than 15 characters are shortened.
     *
     * @return
     */
    @Override
    public String toString() {
        StringBuilder str = new StringBuilder("<");

        if (null != value && value.length() > 0) {
            str.append('\'');

            if (value.length() > 15) {
                str.append(value.substring(0, 15)).append("...");
            } else {
                str.append(value);
            }

            str.append("', ");
        }

        str.append(type).append(", ").append(position).append('>');
        return str.toString();
    }

    /**
     * Returns whether the token is of an operator type or not.
     *
     * @return
     */
    public boolean isOperator() {
        switch (type) {
            case ASIGN:
            case CHOICE:
            case END_OF_RULE:
            case RANGE:
            case L_BRACE:
            case L_BRACK:
            case L_PAREN:
            case R_BRACE:
            case R_BRACK:
            case R_PAREN:
                return true;
            default:
                return false;
        }
    }

    public boolean isType(TokenType t) {
        return type.equals(t);
    }

    public boolean isNotEquals(String[] others) {
        return !isEquals(others);
    }

    public boolean isEquals(String[] others) {
        for (int i = 0; i < others.length; ++i) {
            if (value.equals(others[i])) {
                return true;
            }
        }

        return false;
    }

    public boolean isNotEqual(String other) {
        return !isEqual(other);
    }

    public boolean isEqual(String other) {
        return value.equals(other);
    }

    public static String unquoteString(String str) {
        // @todo move ibnto own class.
        int start = 0;
        int length = str.length();

        if (ScannerHelper.isQuote(str.charAt(start))) {
            start++;
        }

        if (ScannerHelper.isQuote(str.charAt(length - 1))) {
            length--;
        }

        return str.substring(start, length);
    }

}