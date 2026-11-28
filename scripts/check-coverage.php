<?php

declare(strict_types=1);

/**
 * Enforces Rule §10.2 per-path test coverage targets from Clover XML:
 * - app/Http/Controllers/{Merchant,Admin,Auth}/* : 100%
 * - app/Http/Middleware/*                         : 100%
 * - app/Services/* aggregate                      : >= 70%
 * - app/Console/Commands/*, Exceptions/*, Contracts/* : >= 80%
 * - Overall project aggregate                     : >= 70%
 */
$cloverPath = $argv[1] ?? 'build/logs/clover.xml';

if (! is_file($cloverPath)) {
    fwrite(STDERR, "Clover coverage file not found: {$cloverPath}\n");
    exit(1);
}

$xml = simplexml_load_file($cloverPath);
if ($xml === false) {
    fwrite(STDERR, "Failed to parse Clover XML: {$cloverPath}\n");
    exit(1);
}

$rules = [
    'controllers_merchant' => ['label' => 'app/Http/Controllers/Merchant/*', 'pattern' => '#/app/Http/Controllers/Merchant/#', 'min' => 100.0, 'statements' => 0, 'covered' => 0],
    'controllers_admin' => ['label' => 'app/Http/Controllers/Admin/*', 'pattern' => '#/app/Http/Controllers/Admin/#', 'min' => 100.0, 'statements' => 0, 'covered' => 0],
    'controllers_auth' => ['label' => 'app/Http/Controllers/Auth/*', 'pattern' => '#/app/Http/Controllers/Auth/#', 'min' => 100.0, 'statements' => 0, 'covered' => 0],
    'middleware' => ['label' => 'app/Http/Middleware/*', 'pattern' => '#/app/Http/Middleware/#', 'min' => 100.0, 'statements' => 0, 'covered' => 0],
    'services' => ['label' => 'app/Services/* (aggregate)', 'pattern' => '#/app/Services/#', 'min' => 70.0, 'statements' => 0, 'covered' => 0],
    'core' => ['label' => 'app/{Contracts,Exceptions,Console/Commands}/*', 'pattern' => '#/app/(Contracts|Exceptions|Console/Commands)/#', 'min' => 80.0, 'statements' => 0, 'covered' => 0],
];

$totalStatements = 0;
$totalCovered = 0;

foreach ($xml->xpath('//file') ?: [] as $fileNode) {
    $rawName = (string) ($fileNode['name'] ?? '');
    $normalized = '/'.ltrim(str_replace('\\', '/', $rawName), '/');
    $metrics = $fileNode->metrics;

    if (! $metrics instanceof SimpleXMLElement) {
        continue;
    }

    $statements = (int) ($metrics['statements'] ?? 0);
    $covered = (int) ($metrics['coveredstatements'] ?? 0);

    $totalStatements += $statements;
    $totalCovered += $covered;

    foreach ($rules as $key => $rule) {
        if (preg_match($rule['pattern'], $normalized) === 1) {
            $rules[$key]['statements'] += $statements;
            $rules[$key]['covered'] += $covered;
        }
    }
}

$overallPct = $totalStatements > 0 ? round(($totalCovered / $totalStatements) * 100, 1) : 100.0;
$overallMin = 70.0;
$failed = false;

echo "=== Kasirku Coverage Gate (Rule §10.2) ===\n";

foreach ($rules as $rule) {
    if ($rule['statements'] === 0) {
        echo sprintf("  SKIP  %-42s (belum ada statement pada sub-fase ini)\n", $rule['label']);

        continue;
    }

    $pct = round(($rule['covered'] / $rule['statements']) * 100, 1);
    $ok = $pct >= $rule['min'];
    if (! $ok) {
        $failed = true;
    }

    echo sprintf(
        "  %s  %-42s %5.1f%% (min %.1f%%, %d/%d)\n",
        $ok ? 'PASS' : 'FAIL',
        $rule['label'],
        $pct,
        $rule['min'],
        $rule['covered'],
        $rule['statements']
    );
}

if ($overallPct < $overallMin) {
    $failed = true;
}

echo sprintf(
    "  %s  %-42s %5.1f%% (min %.1f%%, %d/%d)\n",
    $overallPct >= $overallMin ? 'PASS' : 'FAIL',
    'Overall app/* aggregate',
    $overallPct,
    $overallMin,
    $totalCovered,
    $totalStatements
);

exit($failed ? 1 : 0);
