<?php

namespace Rpj\Daterangepicker;

use Carbon\Carbon;

class DateHelper
{
    /**
     * Parse date range string and return array of start and end date.
     *
     * @param string|null $dateRange
     * @return array
     */
    public static function parseDateRange($dateRange)
    {
        if (is_null($dateRange) || empty($dateRange)) {
            return [null, null];
        }

        $dates = explode(' to ', $dateRange);
        if (count($dates) == 2) {
            try {
                $startDate = Carbon::createFromFormat('m/d/Y H:i', $dates[0]);
                $endDate = Carbon::createFromFormat('m/d/Y H:i', $dates[1]);
                return [$startDate, $endDate];
            } catch (\Exception $e) {
                return [null, null];
            }
        }

        return [null, null];
    }
}
