<?php
declare(strict_types = 1);

namespace App\Service;

interface HttpRequestTestable
{
    public function test(string $uri, int $requestNumber);
}
