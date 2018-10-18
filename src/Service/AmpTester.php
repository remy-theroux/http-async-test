<?php
declare(strict_types = 1);

namespace App\Service;

use Amp\Artax\Client;
use Amp\Artax\DefaultClient;
use Amp\Artax\HttpException;
use App\Model\Result;
use Symfony\Component\Stopwatch\Stopwatch;
use function Amp\call;
use function Amp\Promise\all;
use function Amp\Promise\wait;

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
     * @param int    $iterations
     *
     * @return Result
     */
    public function test(string $uri, int $iterations): Result
    {
        $this->watcher->start(self::EVENT_NAME);

        $this->httpClient->setOption(Client::OP_DISCARD_BODY, true);
        $client   = $this->httpClient;
        $promises = [];

        for ($i = 0; $i < $iterations; $i++) {
            $promises[] = call(
                function () use ($client, $uri) {
                    // "yield" inside a coroutine awaits the resolution of the promise returned from Client::request(). The generator is then continued.
                    $response = yield $client->request($uri);

                    // Same for the body here. Yielding an Amp\ByteStream\Message buffers the entire message.
                    return $response->getStatus();
                }
            );
        }

        try {
            $status = wait(all($promises));
        } catch (\Exception $error) {
            var_dump($error->getMessage());
        }
        $event = $this->watcher->stop(self::EVENT_NAME);

        $result                 = new Result();
        $result->responseStatus = $status;
        $result->duration       = $event->getDuration();

        return $result;
    }
}
