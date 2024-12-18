<?php

describe('format_business_hours', function () {
    it('formats single day business hours correctly', function () {
        $hours = [
            'monday' => [
                'open'  => '09:00',
                'close' => '17:00',
            ],
        ];

        $formatted = format_business_hours($hours, 'America/New_York');

        $currentAbbreviation = (new DateTime('now', new DateTimeZone('America/New_York')))->format('T');
        expect($formatted)->toBe("Monday 9am-5pm {$currentAbbreviation}");
    });

    it('groups consecutive days with same hours', function () {
        $hours = [
            'monday'    => ['open' => '09:00', 'close' => '17:00'],
            'tuesday'   => ['open' => '09:00', 'close' => '17:00'],
            'wednesday' => ['open' => '09:00', 'close' => '17:00'],
        ];

        $formatted = format_business_hours($hours, 'America/New_York');

        $currentAbbreviation = (new DateTime('now', new DateTimeZone('America/New_York')))->format('T');
        expect($formatted)->toBe("Monday - Wednesday 9am-5pm {$currentAbbreviation}");
    });

    it('separates different hour groups with commas', function () {
        $hours = [
            'monday'    => ['open' => '09:00', 'close' => '17:00'],
            'tuesday'   => ['open' => '09:00', 'close' => '17:00'],
            'wednesday' => ['open' => '10:00', 'close' => '18:00'],
        ];

        $formatted = format_business_hours($hours, 'America/New_York');

        $currentAbbreviation = (new DateTime('now', new DateTimeZone('America/New_York')))->format('T');
        expect($formatted)->toBe("Monday, Tuesday 9am-5pm {$currentAbbreviation}, Wednesday 10am-6pm {$currentAbbreviation}");
    });

    it('uses application timezone when none specified', function () {
        config(['app.local_timezone' => 'UTC']);

        $hours = [
            'monday' => ['open' => '09:00', 'close' => '17:00'],
        ];

        $formatted = format_business_hours($hours);

        expect($formatted)->toBe('Monday 9am-5pm UTC');
    });

    it('throws exception for invalid timezone', function () {
        $hours = [
            'monday' => ['open' => '09:00', 'close' => '17:00'],
        ];

        expect(fn () => format_business_hours($hours, 'Invalid/Timezone'))
            ->toThrow(Exception::class);
    });
});

describe('calculate_read_time', function () {
    it('calculates minimum read time of 1 minute for short content', function () {
        $shortContent = '<p>This is a very short text.</p>';

        expect(calculate_read_time($shortContent))->toBe(1);
    });

    it('calculates read time for longer content', function () {
        // Generate a text with roughly 500 words
        $words = array_fill(0, 500, 'word');
        $content = '<p>' . implode(' ', $words) . '</p>';

        // With 225 words per minute, 500 words should take about 3 minutes
        expect(calculate_read_time($content))->toBe(3);
    });

    it('strips HTML tags before calculating', function () {
        $content = '
            <h1>Title</h1>
            <p>This is <strong>formatted</strong> content with <a href="#">links</a>.</p>
            <div>More content here.</div>
        ';

        // Should only count the actual words, not HTML tags
        expect(calculate_read_time($content))->toBe(1);
    });
});

describe('is_blade_section_empty', function () {
    it('returns true for empty section', function () {
        view()->startSection('test');
        view()->stopSection();

        expect(is_blade_section_empty('test'))->toBeTrue();
    });

    it('returns true for whitespace-only section', function () {
        view()->startSection('test');
        echo "   \n\t  ";
        view()->stopSection();

        expect(is_blade_section_empty('test'))->toBeTrue();
    });

    it('returns false for non-empty section', function () {
        view()->startSection('test');
        echo 'Content';
        view()->stopSection();

        expect(is_blade_section_empty('test'))->toBeFalse();
    });

    it('returns true for non-existent section', function () {
        expect(is_blade_section_empty('non_existent'))->toBeTrue();
    });
});

describe('generate_initials', function () {
    it('generates initials from a simple two-word name', function () {
        $initials = generate_initials('John Doe');
        expect($initials)->toBe('JD');
    });

    it('generates initials from a single word', function () {
        $initials = generate_initials('Madonna');
        expect($initials)->toBe('M');
    });

    it('generates initials from a multi-word name', function () {
        $initials = generate_initials('John James Doe Smith');
        expect($initials)->toBe('JJ'); // Default length of 2
    });

    it('respects custom length parameter', function () {
        $initials = generate_initials('John James Doe Smith', 3);
        expect($initials)->toBe('JJD');
    });

    it('handles empty string', function () {
        $initials = generate_initials('');
        expect($initials)->toBe('');
    });

    it('handles multiple spaces between words', function () {
        $initials = generate_initials('John    Doe');
        expect($initials)->toBe('JD');
    });

    it('handles leading and trailing spaces', function () {
        $initials = generate_initials('  John Doe  ');
        expect($initials)->toBe('JD');
    });

    it('generates initials with special characters', function () {
        $initials = generate_initials('Ángel García');
        expect($initials)->toBe('ÁG');
    });

    it('handles names shorter than requested length', function () {
        $initials = generate_initials('John Doe', 4);
        expect($initials)->toBe('JD'); // Should only return available initials
    });
});

describe('clean_string', function () {
    it('removes extra whitespace', function () {
        $cleaned = clean_string('Hello    World');
        expect($cleaned)->toBe('Hello World');
    });

    it('trims leading and trailing whitespace', function () {
        $cleaned = clean_string('   Hello World   ');
        expect($cleaned)->toBe('Hello World');
    });

    it('converts to lowercase when specified', function () {
        $cleaned = clean_string('Hello World', true);
        expect($cleaned)->toBe('hello world');
    });

    it('maintains case when lowercase is false', function () {
        $cleaned = clean_string('Hello World', false);
        expect($cleaned)->toBe('Hello World');
    });

    it('removes invisible characters', function () {
        $cleaned = clean_string("Hello\x00World\x1F");
        expect($cleaned)->toBe('HelloWorld');
    });

    it('handles empty string', function () {
        $cleaned = clean_string('');
        expect($cleaned)->toBe('');
    });

    it('handles string with only spaces', function () {
        $cleaned = clean_string('     ');
        expect($cleaned)->toBe('');
    });

    it('normalizes different types of whitespace', function () {
        $cleaned = clean_string("Hello\nWorld\tTest");
        expect($cleaned)->toBe('Hello World Test');
    });

    it('handles special characters', function () {
        $cleaned = clean_string('Héllö Wörld');
        expect($cleaned)->toBe('Héllö Wörld');
    });

    it('handles mixed case with lowercase option', function () {
        $cleaned = clean_string('HeLLo WoRLD', true);
        expect($cleaned)->toBe('hello world');
    });
});

describe('remove_trailing_double_slashes', function () {
    it('removes trailing double slashes from a URL', function () {
        $url = 'http://example.com//path//to//resource//';
        $expected = 'http://example.com/path/to/resource/';

        $result = remove_trailing_double_slashes($url);

        expect($result)->toBe($expected);
    });

    it('removes double slashes throughout the URL', function () {
        $url = 'http://example.com////path//to//resource';
        $expected = 'http://example.com/path/to/resource';

        $result = remove_trailing_double_slashes($url);

        expect($result)->toBe($expected);
    });

    it('does nothing to a clean URL', function () {
        $url = 'https://example.com/path/to/resource';
        $expected = 'https://example.com/path/to/resource';

        $result = remove_trailing_double_slashes($url);

        expect($result)->toBe($expected);
    });

    it('handles already clean URLs', function () {
        $url = 'https://example.com/path/to/resource';
        $expected = 'https://example.com/path/to/resource';

        $result = remove_trailing_double_slashes($url);

        expect($result)->toBe($expected);
    });

});
