<?php

declare(strict_types=1);

use Daikazu\MiscHelpers\Utilities\PhpArrayFormatter;

it('exports an empty array', function (): void {
    $exporter = new PhpArrayFormatter;
    $result = $exporter->format([]);
    expect($result)->toBe("<?php\n\nreturn [];\n");
});

it('exports a simple array', function (): void {
    $exporter = new PhpArrayFormatter;
    $result = $exporter->format([1, 2, 3]);
    expect($result)->toBe("<?php\n\nreturn [\n    1,\n    2,\n    3\n];\n");
});

it('exports an associative array', function (): void {
    $exporter = new PhpArrayFormatter;
    $result = $exporter->format(['a' => 1, 'b' => 2]);
    expect($result)->toBe("<?php\n\nreturn [\n    'a' => 1,\n    'b' => 2\n];\n");
});

it('exports a nested array', function (): void {
    $exporter = new PhpArrayFormatter;
    $result = $exporter->format(['a' => [1, 2], 'b' => ['c' => 3]]);
    expect($result)->toBe("<?php\n\nreturn [\n    'a' => [1, 2],\n    'b' => ['c' => 3]\n];\n");
});
