<?php 

namespace App\Helpers;

use Carbon\Carbon;

class Frequency {

    /**
     * Generate timestamps based on flexible intervals.
     *
     * @param string $startDateTime Start datetime (Y-m-d H:i:s)
     * @param string $endDateTime End datetime (Y-m-d H:i:s)
     * @param string $interval Type of interval (e.g., 'hourly', 'n hour', 'daily', 'n day', 'weekly', 'biweekly', 'monthly', etc.)
     * @param array|null $specificDays Specific days of the week (e.g., ['Monday', 'Tuesday']) with time
     * @param string|null $time Specific time (e.g., '12:30') if specific days are given
     * @param array|null $hasIntervalOnSpecificDay Check if specifc days has interval
     * @return array List of timestamps
     */
    public static function generate(
        string $startDateTime, 
        string $endDateTime, 
        string $interval, 
        array $specificDays = null, 
        string $time = null,
        array $hasIntervalOnSpecificDay = []
    ) {
        $timestamps = [];
        $start = Carbon::parse($startDateTime);
        $end = Carbon::parse($endDateTime);

        if ($interval == 'specific_days' && $specificDays && $time) {
            $timeParts = explode(':', $time);            
            while ($start <= $end) {
                foreach ($specificDays as $day) {
                    if (is_array($hasIntervalOnSpecificDay) && array_key_exists('has_interval', $hasIntervalOnSpecificDay) && $hasIntervalOnSpecificDay['has_interval']) {
                        if (isset($hasIntervalOnSpecificDay['interval']) && $hasIntervalOnSpecificDay['interval'] > 0) {
                            $intervalHours = $hasIntervalOnSpecificDay['interval'];

                            $timestampStart = $start->copy()->next($day)->format('Y-m-d') . ' ' . $hasIntervalOnSpecificDay['store_start_time'];
                            $timestampEnd = \App\Http\Controllers\StoreMultiChecklistImportController::adjustEndDate($start->copy()->next($day)->addDay(), $hasIntervalOnSpecificDay['store_end_time']);

                            $startInterval = Carbon::parse($timestampStart);
                            $endInterval = Carbon::parse($timestampEnd);

                            if ($startInterval < $endInterval) {
                                while ($startInterval <= $endInterval) {
                                    if ($startInterval <= $end) {
                                        $timestamps[] = $startInterval->format('Y-m-d H:i:s');
                                    }
                                    $startInterval->addHours($intervalHours);
                                }
                            }
                        }
                    } else {
                        $nextDay = $start->copy()->next($day)->setTime($timeParts[0], $timeParts[1]);
                        if ($nextDay <= $end) {
                            $timestamps[] = $nextDay->format('Y-m-d H:i:s');
                        }
                    }
                }
                $start->addWeek();
            }
        } else {
            switch ($interval) {
                case 'hourly':
                    while ($start <= $end) {
                        $timestamps[] = $start->format('Y-m-d H:i:s');
                        $start->addHour();
                    }
                    break;

                case (preg_match('/(\d+) hour/', $interval, $matches) ? true : false):
                    $hours = $matches[1];
                    while ($start <= $end) {
                        $timestamps[] = $start->format('Y-m-d H:i:s');
                        $start->addHours($hours);
                    }
                    break;

                case 'daily':
                    while ($start <= $end) {
                        $timestamps[] = $start->format('Y-m-d H:i:s');
                        $start->addDay();
                    }
                    break;

                case (preg_match('/(\d+) day/', $interval, $matches) ? true : false):
                    $days = $matches[1];
                    while ($start <= $end) {
                        $timestamps[] = $start->format('Y-m-d H:i:s');
                        $start->addDays($days);
                    }
                    break;

                case 'weekly':
                    while ($start <= $end) {
                        $timestamps[] = $start->format('Y-m-d H:i:s');
                        $start->addWeek();
                    }
                    break;

                case 'biweekly':
                    while ($start <= $end) {
                        $timestamps[] = $start->format('Y-m-d H:i:s');
                        $start->addWeeks(2);
                    }
                    break;

                case 'monthly':
                    while ($start <= $end) {
                        $timestamps[] = $start->format('Y-m-d H:i:s');
                        $start->addMonth();
                    }
                    break;

                case 'bimonthly':
                    while ($start <= $end) {
                        $timestamps[] = $start->format('Y-m-d H:i:s');
                        $start->addMonths(2);
                    }
                    break;

                case 'quarterly':
                    while ($start <= $end) {
                        $timestamps[] = $start->format('Y-m-d H:i:s');
                        $start->addMonths(3);
                    }
                    break;

                case 'semiannually':
                    while ($start <= $end) {
                        $timestamps[] = $start->format('Y-m-d H:i:s');
                        $start->addMonths(6);
                    }
                    break;

                case 'annually':
                    while ($start <= $end) {
                        $timestamps[] = $start->format('Y-m-d H:i:s');
                        $start->addYear();
                    }
                    break;

                default:
                    throw new \InvalidArgumentException('Invalid interval specified.');
            }
        }

        sort($timestamps);
        return $timestamps;
    }

}