<?php

namespace Rpj\Daterangepicker;

use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;
use Rpj\Daterangepicker\DateHelper as Helper;

class Daterangepicker extends Filter
{
    public $component = 'daterangepicker';

    public function apply(NovaRequest $request, $query, $value): \Illuminate\Contracts\Database\Eloquent\Builder
    {
        [$startDate, $endDate] = Helper::parseDateRange($value);

        if ($startDate && $endDate) {
            return $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query;
    }
}
