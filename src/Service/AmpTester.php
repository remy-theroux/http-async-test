<?php
declare(strict_types = 1);

namespace App\Service;

use Amp\Artax\Client;
use Amp\Artax\DefaultClient;
use Amp\Artax\HttpException;
use Amp\Loop;
use App\Model\Result;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 *
 */
class AmpTester implements HttpRequestTestable
{
    private const EVENT_NAME = 'amp-test';

    /** @var DefaultClient */
    private $httpClient;

    /** @var Stopwatch */
    private $watcher;

    /**
     * AmpTester constructor.
     *
     * @param DefaultClient $artaxClient
     * @param Stopwatch     $watcher
     */
    public function __construct(DefaultClient $artaxClient, Stopwatch $watcher)
    {
        $this->httpClient = $artaxClient;
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
        Loop::run(
            function () use ($uri, $requestNumber, &$status) {
                $this->httpClient->setOption(Client::OP_DISCARD_BODY, true);

                try {
                    for ($i = 0; $i < $requestNumber; $i++) {
                        $promises[] = $this->httpClient->request($uri);
                    }

                    $responses = yield $promises;

                    foreach ($responses as $response) {
                        $status[] = $response->getStatus();
                    }
                } catch (HttpException $error) {
                    $status[] = $error->getCode();
                }
            }
        );
        $event = $this->watcher->stop(self::EVENT_NAME);

        $result                 = new Result();
        $result->responseStatus = $status;
        $result->duration       = $event->getDuration();

        return $result;
    }
}
