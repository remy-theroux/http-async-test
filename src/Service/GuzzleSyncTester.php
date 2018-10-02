<?php
declare(strict_types = 1);

namespace App\Service;

use App\Model\Result;
use GuzzleHttp\Client;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 *
 */
class GuzzleSyncTester implements HttpRequestTestable
{
    private const EVENT_NAME = 'guzzle-sync-test';

    /** @var Client */
    private $httpClient;

    /** @var Stopwatch */
    private $watcher;

    /**
     * AmpTester constructor.
     *
     * @param Client $artaxClient
     * @param Stopwatch     $watcher
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
        $this->watcher->start(self::EVENT_NAME);
        for ($i = 0;$i < $requestNumber;$i++) {
            try {
                $response = $this->httpClient->request('GET', $uri);
                $status[] = $response->getStatusCode();
            } catch (\Exception $error) {
                $status[] = '500';
            }
        }
        $event = $this->watcher->stop(self::EVENT_NAME);

        $result                 = new Result();
        $result->responseStatus = $status;
        $result->duration       = $event->getDuration();

        return $result;
    }
}
