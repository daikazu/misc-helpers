<?php

namespace Daikazu\MiscHelpers\Utilities;

use InvalidArgumentException;

class PhpArrayFormatter
{
    private int $indentLevel = 0;
    private string $indentString = '    '; // 4 spaces

    public function format(array $data): string
    {
        return "<?php\n\nreturn " . $this->arrayToString($data) . ";\n";
    }

    private function arrayToString(array $array, bool $inline = false): string
    {
        if (empty($array)) {
            return '[]';
        }

        $isAssoc = $this->isAssociative($array);
        $items = [];
        $this->indentLevel++;

        foreach ($array as $key => $value) {
            $items[] = $this->formatArrayItem($key, $value, $isAssoc);
        }

        $this->indentLevel--;
        $indent = str_repeat($this->indentString, $this->indentLevel);

        if ($inline) {
            return '[' . implode(', ', $items) . ']';
        }

        return "[\n" . implode(",\n", array_map(fn ($item) => $indent . $this->indentString . $item, $items))
            . "\n" . $indent . ']';
    }

    private function formatArrayItem(mixed $key, mixed $value, bool $isAssoc): string
    {
        $formatted = $this->formatValue($value);

        if (! $isAssoc && is_int($key)) {
            return $formatted;
        }

        return $this->formatKey($key) . ' => ' . $formatted;
    }

    private function formatValue(mixed $value): string
    {
        return match (true) {
            is_array($value) => $this->shouldBeInline($value)
                ? $this->arrayToString($value, true)
                : $this->arrayToString($value),
            is_null($value)   => 'null',
            is_bool($value)   => $value ? 'true' : 'false',
            is_int($value)    => (string) $value,
            is_float($value)  => str_contains((string) $value, '.') ? (string) $value : $value . '.0',
            is_string($value) => $this->formatString($value),
            default           => throw new InvalidArgumentException('Unsupported value type: ' . gettype($value)),
        };
    }

    private function formatString(string $value): string
    {
        // Check if the string needs to be wrapped in double quotes
        $needsDoubleQuotes = str_contains($value, "'") ||
            str_contains($value, '\\') ||
            str_contains($value, '\n') ||
            str_contains($value, '\r') ||
            str_contains($value, '\t');

        if ($needsDoubleQuotes) {
            return '"' . addcslashes($value, "\"\\\n\r\t") . '"';
        }

        return "'" . $value . "'";
    }

    private function formatKey(mixed $key): string
    {
        if (is_int($key)) {
            return (string) $key;
        }

        // Always wrap string keys in single quotes for consistency and safety
        return "'" . addcslashes((string) $key, "'\\") . "'";
    }

    private function isAssociative(array $array): bool
    {
        if (empty($array)) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }

    private function shouldBeInline(array $array): bool
    {
        // Check if array is small and simple enough to be displayed inline
        if (empty($array)) {
            return true;
        }

        $count = count($array);
        if ($count > 3) {
            return false;
        }

        foreach ($array as $value) {
            if (is_array($value)) {
                return false;
            }
        }

        return true;
    }
}
