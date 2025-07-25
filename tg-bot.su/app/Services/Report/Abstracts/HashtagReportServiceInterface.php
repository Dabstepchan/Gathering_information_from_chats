<?php

namespace App\Services\Report\Abstracts;

use Illuminate\Support\Carbon;

interface HashtagReportServiceInterface
{
    public function generateReport(?Carbon $startDate = null, ?Carbon $endDate = null): void;
}