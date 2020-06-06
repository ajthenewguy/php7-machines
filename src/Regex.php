<?php
declare(strict_types=1);

namespace Machines;

use Machines\Exceptions\PatternSyntaxException;

/**
 * @property-read string $source
 * @property-read string $flags
 */
class Regex {

    /**
     * @var array<string>
     */
    private static $modifiers = ['i', 'm', 's', 'x', 'A', 'D', 'S', 'U', 'X', 'J', 'u'];

    /**
     * @var array<string>
     */
    private $pattern_modifiers;

    /**
     * @var string
     */
    private $pattern;


    /**
     * @param string $pattern
     */
    public function __construct(string $pattern)
    {
        $this->validatePattern($pattern);
        $this->pattern = $pattern;
        $this->pattern_modifiers = static::modifiers($pattern);
    }

    /**
     * @param array<string> $subjects
     * @param boolean $invert
     * @return array<string,null>
     */
    public function grep(array $subjects, bool $invert = false): array
    {
        $flags = $invert ? PREG_GREP_INVERT : 0;
        return preg_grep($this->pattern, $subjects, $flags);
    }

    /**
     * @param string $subject
     * @param int $flags
     * @param int $offset
     * @return array<string>|null
     */
    public function match(string $subject, int $flags = 0, int $offset = 0): ?array
    {
        $matches = [];
        preg_match($this->pattern, $subject, $matches, $flags, $offset);

        return $matches;
    }

    /**
     * @param string $subject
     * @param int $flags
     * @param int $offset
     * @return array<string>|null
     */
    public function matchAll(string $subject, int $flags = 0, int $offset = 0): ?array
    {
        $matches = [];
        preg_match_all($this->pattern, $subject, $matches, $flags, $offset);

        return $matches;
    }

    /**
     * @param string $replacement
     * @param string $subject
     * @param int $limit
     * @param int $count
     * @return string|null
     */
    public function replace(string $replacement, string $subject, int $limit = -1, int &$count = 0): ?string
    {
        return preg_replace($this->pattern, $replacement, $subject, $limit, $count);
    }

    /**
     * @param string $subject
     * @param int $limit
     * @param int $flags
     * @return array<string>
     */
    public function split(string $subject, int $limit = -1, int $flags = 0): array
    {
        $out = preg_split($this->pattern, $subject, $limit, $flags);

        if (false === $out) {
            throw new PatternSyntaxException(null, $this->pattern);
        }

        return $out;
    }

    /**
     * @param string $subject
     * @param int $offset
     * @return boolean
     */
    public function test(string $subject, int $offset = 0): bool
    {
        $out = preg_match($this->pattern, $subject, $matches, 0, $offset);

        if (false === $out) {
            throw new PatternSyntaxException($offset ? 'invalid offset' : null, $this->pattern);
        }

        return $out === 1;
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function __get(string $name): ?string
    {
        if ($name === 'flags') {
            return implode($this->pattern_modifiers);
        }

        if ($name === 'source') {
            return $this->pattern;
        }

        return null;
    }

    /**
     * @param string $string
     * @return bool
     */
    public static function isValid(string $string): bool
    {
        $modifiers = static::modifiers($string);
        $right_delimeter_index = (count($modifiers) + 1) * -1;
        if ($string[0] !== $string[$right_delimeter_index]) {
            $brackets = [
                '(' => ')',
                '{' => '}',
                '[' => ']',
                '<' => '>'
            ];
            if (in_array($string[$right_delimeter_index], $brackets)) {
                if ($string[0] !== array_search($string[$right_delimeter_index], $brackets)) {
                    return false;
                }
            } else {
                return false;
            }
        }

        return preg_match('/[^0-9a-z\s\\\]/i', $string[0]) === 1;
    }

    /**
     * @param string $string
     * @return array<string>
     */
    public static function modifiers(string $string)
    {
        $index = -1;
        $modifiers = [];
        while (in_array($string[$index], static::$modifiers)) {
            if (in_array($string[$index], $modifiers)) {
                throw new PatternSyntaxException(sprintf('duplicate modifier "%s"', $string[$index]), $string);
            }
            $modifiers[] = $string[$index];
            --$index;
        }

        return $modifiers;
    }

    /**
     * @param string $pattern
     */
    private function validatePattern(string $pattern): void
    {
        if (!static::isValid($pattern)) {
            throw new PatternSyntaxException('invalid regular expression pattern', $pattern);
        }
    }
}