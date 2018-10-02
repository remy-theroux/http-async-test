#!/usr/bin/env php
<?php
declare(strict_types = 1);

require __DIR__ . '/vendor/autoload.php';

use Amp\Artax\DefaultClient;
use App\Command\BenchmarkRequestsCommand;
use App\Service\AmpTester;
use App\Service\GuzzleAsyncTester;
use App\Service\GuzzleSyncTester;
use GuzzleHttp\Client;
use Symfony\Component\Console\Application;
use Symfony\Component\Stopwatch\Stopwatch;

$application = new Application();

// No symfony bootstrap, hey it's a test dude. So here's my DI manager
$stopWatch = new Stopwatch();
$ampTester = new AmpTester(new DefaultClient(), $stopWatch);

$guzzleClient = new Client();
$guzzleSyncTester = new GuzzleSyncTester($guzzleClient, $stopWatch);
$guzzleAsyncTester = new GuzzleAsyncTester($guzzleClient, $stopWatch);

$application->add(new BenchmarkRequestsCommand($ampTester, $guzzleSyncTester, $guzzleAsyncTester));

$application->run();
