<?php

use JeffersonGoncalves\CookieConsent\Tests\TestCase;
use Symfony\Component\Process\Process;

uses(TestCase::class)->in(__DIR__);

/** Runs JS in Node from a file: `node -e` hits the Windows command line length limit. */
function runNode(string $js): string
{
    $file = tempnam(sys_get_temp_dir(), 'cookie-consent-').'.js';
    file_put_contents($file, $js);

    try {
        return (new Process(['node', $file]))->mustRun()->getOutput();
    } finally {
        @unlink($file);
    }
}
