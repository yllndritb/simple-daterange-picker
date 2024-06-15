<?php

namespace Rpj\Daterangepicker;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;
use Rpj\Daterangepicker\DateHelper as Helper;

class Daterangepicker extends Filter
{
    private Carbon|null $minDate = null;
    private Carbon|null $maxDate = null;
    private array|null $ranges = null;
    private bool $dateTimeRange = false;

    public function __construct(
        private string $column,
        private string $default = Helper::TODAY,
        private string $orderByColumn = 'id',
        private string $orderByDir = 'asc',
    )
    {
    }

    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'daterangepicker';

    /**
     * Apply the filter to the given query.
     *
     * @param NovaRequest $request
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     * @return Builder
     * @throws Exception
     */
    public function apply(NovaRequest $request, $query, $value): Builder
    {
        [$start, $end] = Helper::getParsedDatesGroupedRanges($value);

        if ($start && $end) {
            return $query->whereBetween($this->column, [$start, $end])
                ->orderBy($this->orderByColumn, $this->orderByDir);
        }

        return $query;
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(NovaRequest $request): array|null
    {
        if (!$this->ranges) {
            $this->setRanges(Helper::defaultRanges());
        }

        return $this->ranges;
    }

    /**
     * Set the default options for the filter.
     *
     * @return array|mixed
     */
    public function default(): string|null
    {
        [$start, $end] = Helper::getParsedDatesGroupedRanges($this->default);

        if ($start && $end && !$this->dateTimeRange) {
            return $start->format('Y-m-d') . ' to ' . $end->format('Y-m-d');
        }

        if ($start && $end && $this->dateTimeRange) {
            return $start->format('Y-m-d H:i') . ' to ' . $end->format('Y-m-d H:i');
        }

        return null;
    }

    public function setMinDate(Carbon $minDate): self
    {
        $this->minDate = $minDate;

        if ($this->maxDate && $this->minDate->gt($this->maxDate)) {
            throw new Exception('Date range picker: minDate must be lower or equals than maxDate.');
        }

        return $this;
    }


    /**
     * @param bool $dateTimeRange
     * @return $this
     */
    public function useDateTimeRange(bool $dateTimeRange = true): self
    {
        $this->dateTimeRange = $dateTimeRange;

        return $this;
    }

    public function setMaxDate(Carbon $maxDate): self
    {
        $this->maxDate = $maxDate;

        if ($this->minDate && $this->maxDate->lt($this->minDate)) {
            throw new Exception('Date range picker: maxDate must be greater or equals than minDate.');
        }

        return $this;
    }

    /**
     * @param array $ranges
     * @return Daterangepicker
     */
    public function setRanges(array $ranges): self
    {
        $result = [];
        $dateTimeRange = $this->dateTimeRange;
        $result = collect($ranges)->mapWithKeys(function (array $item, string $key) use ($dateTimeRange) {
            return [$key => (collect($item)->map(function (Carbon $date) use ($dateTimeRange) {
                if ($dateTimeRange) {
                    return $date->format('Y-m-d H:i');
                } else {
                    return $date->format('Y-m-d');
                }
            }))];
        })->toArray();

        $this->ranges = $result;

        return $this;
    }

    /**
     * Convert the filter to its JSON representation.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        if ($this->dateTimeRange) {
            return array_merge(parent::jsonSerialize(), [
                'minDate' => $this?->minDate?->format('Y-m-d H:i'),
                'maxDate' => $this?->maxDate?->format('Y-m-d H:i'),
                'dateTimeRange' => $this->dateTimeRange,
            ]);
        } else {
            return array_merge(parent::jsonSerialize(), [
                'minDate' => $this?->minDate?->format('Y-m-d'),
                'maxDate' => $this?->maxDate?->format('Y-m-d'),
                'dateTimeRange' => $this->dateTimeRange,
            ]);
        }
    }
}
