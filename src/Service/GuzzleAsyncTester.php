<?php
declare(strict_types = 1);

namespace App\Service;

use App\Model\Result;
use GuzzleHttp\Client;
use function GuzzleHttp\Promise\all;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\Stopwatch\Stopwatch;


class GuzzleAsyncTester
{
    private const EVENT_NAME = 'guzzle-async-test';

    /** @var Client */
    private $httpClient;

    /** @var Stopwatch */
    private $watcher;

    /**
     * AmpTester constructor.
     *
     * @param Client    $guzzleClient
     * @param Stopwatch $watcher
     */
    public function __construct(Client $guzzleClient, Stopwatch $watcher)
    {
        $this->httpClient = $guzzleClient;
        $this->watcher    = $watcher;
    }

    /**
     * @param string $uri
     * @param int    $requestNumber
     *
     * @return Result
     */
    public function test(string $uri, int $requestNumber): Result
    {
        $status = [];
        $promises = [];
        $this->watcher->start(self::EVENT_NAME);
        for ($i = 0; $i < $requestNumber; $i++) {
            $promises[] = $this->httpClient->getAsync($uri);
        }

        all($promises)->then(function (array $responses) use (&$status) {
            /** @var Response $response */
            foreach ($responses as $response) {
                $status[] = $response->getStatusCode();
            }
        })->wait();

        $event = $this->watcher->stop(self::EVENT_NAME);

        $result                 = new Result();
        $result->responseStatus = $status;
        $result->duration       = $event->getDuration();

        return $result;
    }
}
