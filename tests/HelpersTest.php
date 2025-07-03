<?php

declare(strict_types=1);

describe('format_business_hours', function (): void {
    it('formats single day business hours correctly', function (): void {
        $hours = [
            'monday' => [
                'open'  => '09:00',
                'close' => '17:00',
            ],
        ];

        $formatted = format_business_hours($hours, 'America/New_York');

        $currentAbbreviation = (new DateTime('now', new DateTimeZone('America/New_York')))->format('T');
        expect($formatted)->toBe('Monday 9am-5pm ' . $currentAbbreviation);
    });

    it('groups consecutive days with same hours', function (): void {
        $hours = [
            'monday'    => ['open' => '09:00', 'close' => '17:00'],
            'tuesday'   => ['open' => '09:00', 'close' => '17:00'],
            'wednesday' => ['open' => '09:00', 'close' => '17:00'],
        ];

        $formatted = format_business_hours($hours, 'America/New_York');

        $currentAbbreviation = (new DateTime('now', new DateTimeZone('America/New_York')))->format('T');
        expect($formatted)->toBe('Monday - Wednesday 9am-5pm ' . $currentAbbreviation);
    });

    it('separates different hour groups with commas', function (): void {
        $hours = [
            'monday'    => ['open' => '09:00', 'close' => '17:00'],
            'tuesday'   => ['open' => '09:00', 'close' => '17:00'],
            'wednesday' => ['open' => '10:00', 'close' => '18:00'],
        ];

        $formatted = format_business_hours($hours, 'America/New_York');

        $currentAbbreviation = (new DateTime('now', new DateTimeZone('America/New_York')))->format('T');
        expect($formatted)->toBe(sprintf('Monday, Tuesday 9am-5pm %s, Wednesday 10am-6pm %s', $currentAbbreviation, $currentAbbreviation));
    });

    it('uses application timezone when none specified', function (): void {
        config(['app.local_timezone' => 'UTC']);

        $hours = [
            'monday' => ['open' => '09:00', 'close' => '17:00'],
        ];

        $formatted = format_business_hours($hours);

        expect($formatted)->toBe('Monday 9am-5pm UTC');
    });

    it('throws exception for invalid timezone', function (): void {
        $hours = [
            'monday' => ['open' => '09:00', 'close' => '17:00'],
        ];

        expect(fn (): string => format_business_hours($hours, 'Invalid/Timezone'))
            ->toThrow(Exception::class);
    });
});

describe('calculate_read_time', function (): void {
    it('calculates minimum read time of 1 minute for short content', function (): void {
        $shortContent = '<p>This is a very short text.</p>';

        expect(calculate_read_time($shortContent))->toBe(1);
    });

    it('calculates read time for longer content', function (): void {
        // Generate a text with roughly 500 words
        $words = array_fill(0, 500, 'word');
        $content = '<p>' . implode(' ', $words) . '</p>';

        // With 225 words per minute, 500 words should take about 3 minutes
        expect(calculate_read_time($content))->toBe(3);
    });

    it('strips HTML tags before calculating', function (): void {
        $content = '
            <h1>Title</h1>
            <p>This is <strong>formatted</strong> content with <a href="#">links</a>.</p>
            <div>More content here.</div>
        ';

        // Should only count the actual words, not HTML tags
        expect(calculate_read_time($content))->toBe(1);
    });
});

describe('is_blade_section_empty', function (): void {
    it('returns true for empty section', function (): void {
        view()->startSection('test');
        view()->stopSection();

        expect(is_blade_section_empty('test'))->toBeTrue();
    });

    it('returns true for whitespace-only section', function (): void {
        view()->startSection('test');
        echo "   \n\t  ";
        view()->stopSection();

        expect(is_blade_section_empty('test'))->toBeTrue();
    });

    it('returns false for non-empty section', function (): void {
        view()->startSection('test');
        echo 'Content';
        view()->stopSection();

        expect(is_blade_section_empty('test'))->toBeFalse();
    });

    it('returns true for non-existent section', function (): void {
        expect(is_blade_section_empty('non_existent'))->toBeTrue();
    });
});

describe('generate_initials', function (): void {
    it('generates initials from a simple two-word name', function (): void {
        $initials = generate_initials('John Doe');
        expect($initials)->toBe('JD');
    });

    it('generates initials from a single word', function (): void {
        $initials = generate_initials('Madonna');
        expect($initials)->toBe('M');
    });

    it('generates initials from a multi-word name', function (): void {
        $initials = generate_initials('John James Doe Smith');
        expect($initials)->toBe('JJ'); // Default length of 2
    });

    it('respects custom length parameter', function (): void {
        $initials = generate_initials('John James Doe Smith', 3);
        expect($initials)->toBe('JJD');
    });

    it('handles empty string', function (): void {
        $initials = generate_initials('');
        expect($initials)->toBe('');
    });

    it('handles multiple spaces between words', function (): void {
        $initials = generate_initials('John    Doe');
        expect($initials)->toBe('JD');
    });

    it('handles leading and trailing spaces', function (): void {
        $initials = generate_initials('  John Doe  ');
        expect($initials)->toBe('JD');
    });

    it('generates initials with special characters', function (): void {
        $initials = generate_initials('Ángel García');
        expect($initials)->toBe('ÁG');
    });

    it('handles names shorter than requested length', function (): void {
        $initials = generate_initials('John Doe', 4);
        expect($initials)->toBe('JD'); // Should only return available initials
    });
});

describe('clean_string', function (): void {
    it('removes extra whitespace', function (): void {
        $cleaned = clean_string('Hello    World');
        expect($cleaned)->toBe('Hello World');
    });

    it('trims leading and trailing whitespace', function (): void {
        $cleaned = clean_string('   Hello World   ');
        expect($cleaned)->toBe('Hello World');
    });

    it('converts to lowercase when specified', function (): void {
        $cleaned = clean_string('Hello World', true);
        expect($cleaned)->toBe('hello world');
    });

    it('maintains case when lowercase is false', function (): void {
        $cleaned = clean_string('Hello World', false);
        expect($cleaned)->toBe('Hello World');
    });

    it('removes invisible characters', function (): void {
        $cleaned = clean_string("Hello\x00World\x1F");
        expect($cleaned)->toBe('HelloWorld');
    });

    it('handles empty string', function (): void {
        $cleaned = clean_string('');
        expect($cleaned)->toBe('');
    });

    it('handles string with only spaces', function (): void {
        $cleaned = clean_string('     ');
        expect($cleaned)->toBe('');
    });

    it('normalizes different types of whitespace', function (): void {
        $cleaned = clean_string("Hello\nWorld\tTest");
        expect($cleaned)->toBe('Hello World Test');
    });

    it('handles special characters', function (): void {
        $cleaned = clean_string('Héllö Wörld');
        expect($cleaned)->toBe('Héllö Wörld');
    });

    it('handles mixed case with lowercase option', function (): void {
        $cleaned = clean_string('HeLLo WoRLD', true);
        expect($cleaned)->toBe('hello world');
    });
});

describe('remove_trailing_double_slashes', function (): void {
    it('removes trailing double slashes from a URL', function (): void {
        $url = 'http://example.com//path//to//resource//';
        $expected = 'http://example.com/path/to/resource/';

        $result = remove_trailing_double_slashes($url);

        expect($result)->toBe($expected);
    });

    it('removes double slashes throughout the URL', function (): void {
        $url = 'http://example.com////path//to//resource';
        $expected = 'http://example.com/path/to/resource';

        $result = remove_trailing_double_slashes($url);

        expect($result)->toBe($expected);
    });

    it('does nothing to a clean URL', function (): void {
        $url = 'https://example.com/path/to/resource';
        $expected = 'https://example.com/path/to/resource';

        $result = remove_trailing_double_slashes($url);

        expect($result)->toBe($expected);
    });

    it('handles already clean URLs', function (): void {
        $url = 'https://example.com/path/to/resource';
        $expected = 'https://example.com/path/to/resource';

        $result = remove_trailing_double_slashes($url);

        expect($result)->toBe($expected);
    });

});

describe('convert_to_iso_8601_duration', function (): void {
    it('converts hours, minutes, and seconds format', function (): void {
        expect(convert_to_iso_8601_duration('1:30:45'))->toBe('PT1H30M45S');
    });

    it('converts minutes and seconds format', function (): void {
        expect(convert_to_iso_8601_duration('30:45'))->toBe('PT30M45S');
    });

    it('converts seconds only format', function (): void {
        expect(convert_to_iso_8601_duration('45'))->toBe('PT45S');
    });

    it('handles zero hours', function (): void {
        expect(convert_to_iso_8601_duration('0:30:45'))->toBe('PT30M45S');
    });

    it('handles zero minutes', function (): void {
        expect(convert_to_iso_8601_duration('1:0:45'))->toBe('PT1H45S');
    });

    it('handles zero seconds', function (): void {
        expect(convert_to_iso_8601_duration('1:30:0'))->toBe('PT1H30M');
    });

    it('handles all zeros', function (): void {
        expect(convert_to_iso_8601_duration('0:0:0'))->toBe('PT0S');
    });

    it('handles single zero', function (): void {
        expect(convert_to_iso_8601_duration('0'))->toBe('PT0S');
    });

    it('handles double zero format', function (): void {
        expect(convert_to_iso_8601_duration('0:0'))->toBe('PT0S');
    });

    it('handles large values', function (): void {
        expect(convert_to_iso_8601_duration('25:75:90'))->toBe('PT25H75M90S');
    });

    it('handles single digit values', function (): void {
        expect(convert_to_iso_8601_duration('1:2:3'))->toBe('PT1H2M3S');
    });

    it('handles only hours and minutes', function (): void {
        expect(convert_to_iso_8601_duration('2:30:0'))->toBe('PT2H30M');
    });

    it('handles only hours and seconds', function (): void {
        expect(convert_to_iso_8601_duration('2:0:30'))->toBe('PT2H30S');
    });

    it('handles only minutes and seconds with zero hours', function (): void {
        expect(convert_to_iso_8601_duration('0:15:30'))->toBe('PT15M30S');
    });

    it('handles empty string gracefully', function (): void {
        expect(convert_to_iso_8601_duration(''))->toBe('PT0S');
    });

    it('handles string with leading zeros', function (): void {
        expect(convert_to_iso_8601_duration('01:05:09'))->toBe('PT1H5M9S');
    });

    it('handles very large hour values', function (): void {
        expect(convert_to_iso_8601_duration('100:30:45'))->toBe('PT100H30M45S');
    });

    it('handles maximum typical values', function (): void {
        expect(convert_to_iso_8601_duration('23:59:59'))->toBe('PT23H59M59S');
    });
});
