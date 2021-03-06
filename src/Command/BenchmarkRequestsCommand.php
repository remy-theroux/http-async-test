<?php
declare(strict_types = 1);

namespace App\Command;

use Amp\Artax\DefaultClient;
use App\Service\AmpTester;
use App\Service\GuzzleAsyncTester;
use App\Service\GuzzleSyncTester;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class BenchmarkRequestsCommand extends Command
{
    private const DEFAULT_REQUESTS_NUMBER = 20;
    private const DEFAULT_URI = 'https://httpstat.us/200?sleep=1500';

    /** @var AmpTester  */
    private $ampTester;

    /** @var GuzzleSyncTester  */
    private $guzzleSyncTester;

    /** @var GuzzleAsyncTester  */
    private $guzzleAsyncTester;

    /**
     * BenchmarkRequestsCommand constructor.
     *
     * @param AmpTester         $ampTester
     * @param GuzzleSyncTester  $guzzleSyncTester
     * @param GuzzleAsyncTester $guzzleAsyncTester
     */
    public function __construct(AmpTester $ampTester, GuzzleSyncTester $guzzleSyncTester, GuzzleAsyncTester $guzzleAsyncTester)
    {
        parent::__construct();

        $this->ampTester         = $ampTester;
        $this->guzzleSyncTester  = $guzzleSyncTester;
        $this->guzzleAsyncTester = $guzzleAsyncTester;
    }

    protected function configure() {
        $this
            ->setName('benchmark')
            ->setDescription('Benchmarks multiple http requests.')
            ->addOption('iterations', 'i', InputOption::VALUE_OPTIONAL, 'Indicates number of request iterations', self::DEFAULT_REQUESTS_NUMBER)
            ->addOption('uri', 'u', InputOption::VALUE_OPTIONAL, 'URI that will be requested (GET request)', self::DEFAULT_URI);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $iterations = (int) $input->getOption('iterations');
        $uri        = $input->getOption('uri');

        $guzzleSyncResult  = $this->guzzleSyncTester->test($uri, $iterations);
        $guzzleAsyncResult = $this->guzzleAsyncTester->test($uri, $iterations);
        $ampResult         = $this->ampTester->test($uri, $iterations);

        $table = new Table($output);
        $table->setHeaders(['Method ', 'Duration', 'Requests OK', 'Requests ERROR'])->setRows(
                [
                    ['Guzzle SYNC', $guzzleSyncResult->getDurationLabel(), $guzzleSyncResult->getSuccessStatusNumber(), $guzzleSyncResult->getErrorStatusNumber()],
                    ['Guzzle ASYNC', $guzzleAsyncResult->getDurationLabel(), $guzzleAsyncResult->getSuccessStatusNumber(), $guzzleAsyncResult->getErrorStatusNumber()],
                    ['AMP', $ampResult->getDurationLabel(), $ampResult->getSuccessStatusNumber(), $ampResult->getErrorStatusNumber()],
                ]
            );
        $table->render();

        return OutputInterface::OUTPUT_NORMAL;
    }
}
