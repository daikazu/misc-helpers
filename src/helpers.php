<?php

declare(strict_types=1);

if (! function_exists('format_business_hours')) {

    /**
     * Formats business hours for display, grouping similar hours and converting times to a specified timezone.
     *
     * @param  array  $hours  Array of business hours.
     * @param  ?string  $timezone  Optional. Timezone for converting hours. Defaults to application's local timezone.
     * @return string Formatted business hours.
     *
     * @throws Exception
     */
    function format_business_hours(array $hours, ?string $timezone = null): string
    {
        if (! $timezone) {
            $timezone = config('app.local_timezone');
        }

        $formatTime = function ($time) use ($timezone): string {
            $date = new DateTime($time, new DateTimeZone($timezone));

            return $date->format('ga'); // Converts to am/pm format
        };

        $getTimezoneAbbreviation = function () use ($timezone): string {
            $dateTime = new DateTime('now', new DateTimeZone($timezone));

            return $dateTime->format('T');
        };

        $groupHours = function ($hours): array {
            $grouped = [];
            foreach ($hours as $day => $times) {
                $key = sprintf('%s-%s', $times['open'], $times['close']);
                if (! isset($grouped[$key])) {
                    $grouped[$key] = [];
                }

                $grouped[$key][] = ucfirst($day);
            }

            return $grouped;
        };

        $groupedHours = $groupHours($hours);
        $parts = [];
        $timezoneAbbreviation = $getTimezoneAbbreviation();

        foreach ($groupedHours as $times => [$firstDay]) {
            [$open, $close] = explode('-', $times);
            $open = $formatTime($open);
            $close = $formatTime($close);

            // Find ranges or single days
            if (count($groupedHours[$times]) > 2) {
                $lastDay = end($groupedHours[$times]);
                $part = sprintf('%s - %s %s-%s %s', $firstDay, $lastDay, $open, $close, $timezoneAbbreviation);
            } else {
                $days = implode(', ', array_map('ucfirst', $groupedHours[$times]));
                $part = sprintf('%s %s-%s %s', $days, $open, $close, $timezoneAbbreviation);
            }

            $parts[] = $part;
        }

        return implode(', ', $parts);
    }
}

if (! function_exists('calculate_read_time')) {
    function calculate_read_time(string $htmlContent, int $averageReadingSpeed = 225): int
    {
        // Remove script and style content
        $htmlContent = preg_replace('/<(script|style)\b[^>]*>(.*?)<\/\1>/is', '', $htmlContent);

        // Remove HTML tags to isolate the text
        $textContent = strip_tags((string) $htmlContent);

        // Decode HTML entities
        $textContent = html_entity_decode($textContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Count the number of words in the text using Unicode regex
        $wordCount = preg_match_all('/\p{L}+/u', $textContent, $matches);

        // Avoid division by zero
        if ($averageReadingSpeed <= 0) {
            $averageReadingSpeed = 225;
        }

        // Calculate the estimated read time in minutes
        return (int) ceil($wordCount / $averageReadingSpeed);
    }
}

if (! function_exists('is_blade_section_empty')) {
    function is_blade_section_empty($section): bool
    {
        return in_array(trim(view()->getSections()[$section] ?? ''), ['', '0'], true);
    }
}

if (! function_exists('generate_initials')) {
    /**
     * Generates initials from a full name.
     *
     * @param  int  $length  Maximum number of initials
     */
    function generate_initials(string $name, int $length = 2): string
    {
        $words = array_filter(explode(' ', $name));
        $initials = '';

        foreach ($words as $word) {
            $initials .= mb_strtoupper(mb_substr($word, 0, 1));

            if (mb_strlen($initials) >= $length) {
                break;
            }
        }

        return $initials;
    }

}

if (! function_exists('clean_string')) {
    /**
     * Cleans a string by removing specific characters and normalizing whitespace.
     */
    function clean_string(string $string, bool $lowercase = false): string
    {
        // Remove invisible characters
        $string = preg_replace('/[\x00-\x08\x0B-\x1F\x7F]/u', '', $string);

        // Normalize whitespace (including tabs and newlines)
        $string = preg_replace('/\s+/u', ' ', trim((string) $string));

        return $lowercase ? mb_strtolower((string) $string) : $string;
    }
}

if (! function_exists('remove_trailing_double_slashes')) {
    function remove_trailing_double_slashes(string $url): string
    {
        return preg_replace_callback(
            '#^(https?://)?(.*)$#',
            function (array $matches) {
                $protocol = $matches[1]; // No need for null coalescing
                $rest = preg_replace('#/{2,}#', '/', $matches[2]); // Normalize slashes in the rest

                return $protocol . $rest;
            },
            $url
        );
    }
}

/**
 * Convert time duration string to ISO 8601 format
 *
 * @param  string  $duration  Time duration in format like "1:30:34" or "30:34" or "34"
 * @return string ISO 8601 duration format like "PT1H30M34S"
 */
function convert_to_iso_8601_duration(string $duration): string
{
    $parts = explode(':', $duration);
    $parts = array_reverse($parts); // Start from seconds

    $seconds = (int) ($parts[0] ?? 0);
    $minutes = (int) ($parts[1] ?? 0);
    $hours = (int) ($parts[2] ?? 0);

    $iso8601 = 'PT';

    if ($hours > 0) {
        $iso8601 .= $hours . 'H';
    }

    if ($minutes > 0) {
        $iso8601 .= $minutes . 'M';
    }

    if ($seconds > 0) {
        $iso8601 .= $seconds . 'S';
    }

    // If no time components, return PT0S
    if ($iso8601 === 'PT') {
        $iso8601 = 'PT0S';
    }

    return $iso8601;
}
