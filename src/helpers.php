<?php

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

        $formatTime = function ($time) use ($timezone) {
            $date = new DateTime($time, new DateTimeZone($timezone));

            return $date->format('ga'); // Converts to am/pm format
        };

        $getTimezoneAbbreviation = function () use ($timezone) {
            $dateTime = new DateTime('now', new DateTimeZone($timezone));

            return $dateTime->format('T');
        };

        $groupHours = function ($hours) {
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
                $part = "{$firstDay} - {$lastDay} {$open}-{$close} {$timezoneAbbreviation}";
            } else {
                $days = implode(', ', array_map('ucfirst', $groupedHours[$times]));
                $part = "{$days} {$open}-{$close} {$timezoneAbbreviation}";
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
        $textContent = strip_tags($htmlContent);

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
        return empty(trim(view()->getSections()[$section] ?? ''));
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

    if (! function_exists('clean_string')) {
        /**
         * Cleans a string by removing specific characters and normalizing whitespace.
         */
        function clean_string(string $string, bool $lowercase = false): string
        {
            // Remove invisible characters
            $string = preg_replace('/[\x00-\x08\x0B-\x1F\x7F]/u', '', $string);

            // Normalize whitespace (including tabs and newlines)
            $string = preg_replace('/\s+/u', ' ', trim($string));

            return $lowercase ? mb_strtolower($string) : $string;
        }
    }

}
