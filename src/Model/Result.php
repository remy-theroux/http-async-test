<?php
declare(strict_types = 1);

namespace App\Model;

/**
 *
 */
class Result
{
    /** @var int */
    public $duration;

    /** @var array|int[] */
    public $responseStatus;

    /**
     * @return string
     */
    public function getDurationLabel(): string
    {
        return (int) ($this->duration / 1000) . ',' . ($this->duration - (int) ($this->duration / 1000)) . ' s';
    }

    /**
     * @return int
     */
    public function getErrorStatusNumber()
    {
        return \count(array_filter($this->responseStatus, function(int $statusCode) { return 200 !== $statusCode; }));
    }

    /**
     * @return int
     */
    public function getSuccessStatusNumber()
    {
        return \count(array_filter($this->responseStatus, function(int $statusCode) { return 200 === $statusCode; }));
    }
}
