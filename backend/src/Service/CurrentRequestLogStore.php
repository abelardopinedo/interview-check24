<?php

namespace App\Service;

use App\Entity\RequestLog;

class CurrentRequestLogStore
{
    private ?RequestLog $requestLog = null;

    public function setRequestLog(RequestLog $log): void
    {
        $this->requestLog = $log;
    }

    public function getRequestLog(): ?RequestLog
    {
        return $this->requestLog;
    }
}
