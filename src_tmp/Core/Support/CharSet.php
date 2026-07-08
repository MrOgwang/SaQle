<?php

namespace SaQle\Core\Support;

enum CharSet: int {
    case LETTERS     = 1 << 0;
    case DIGITS      = 1 << 1;
    case WHITESPACE  = 1 << 2;
    case PUNCTUATION = 1 << 3;
    case SYMBOLS     = 1 << 4;
    case EMOJI       = 1 << 5;

    case ALPHA           = self::LETTERS;
    case ALPHA_NUMERIC   = self::LETTERS | self::DIGITS;
    case TEXT            = self::LETTERS | self::DIGITS | self::WHITESPACE | self::PUNCTUATION;
}