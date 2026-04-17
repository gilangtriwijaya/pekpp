<?php

namespace App\Services\Analytics;

class AnalyticsAggregateWriter
{
    public function rebuildFull()
    {
        // TODO: implement full rebuild logic (dispatch RebuildAnalyticsAggregatesJob)
    }

    public function updateForRecord(array $record)
    {
        // TODO: implement incremental update for a single F02/F03 record
    }
}
