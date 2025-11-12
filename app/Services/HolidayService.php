<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class HolidayService
{
    /**
     * Check if the given date is a holiday based on Google Calendar.
     * Caches results per calendar per month to minimize API calls.
     */
    public function isHoliday(\DateTimeInterface $date): bool
    {
        $calendarId = env('HOLIDAY_CALENDAR_ID');
        $apiKey = env('GOOGLE_CALENDAR_API_KEY');
        if (empty($calendarId) || empty($apiKey)) {
            return false; // Fail-open if not configured
        }

        $ymKey = $date->format('Y-m');
        $cacheKey = 'holidays:' . md5($calendarId) . ':' . $ymKey;
        $dates = Cache::remember($cacheKey, now()->addHours(6), function () use ($calendarId, $apiKey, $date) {
            $startOfMonth = (new \DateTimeImmutable($date->format('Y-m-01')))->setTime(0, 0, 0);
            $endOfMonth = (new \DateTimeImmutable($date->format('Y-m-t')))->setTime(23, 59, 59);

            $response = Http::get('https://www.googleapis.com/calendar/v3/calendars/' . urlencode($calendarId) . '/events', [
                'key' => $apiKey,
                'timeMin' => $startOfMonth->format(\DateTime::ATOM),
                'timeMax' => $endOfMonth->format(\DateTime::ATOM),
                'singleEvents' => 'true',
                'maxResults' => 2500,
                'orderBy' => 'startTime',
            ]);

            if (!$response->ok()) {
                return [];
            }

            $items = $response->json('items') ?? [];
            $holidayDates = [];
            foreach ($items as $event) {
                // Public holiday calendars typically use all-day events with date (not dateTime)
                $startDate = $event['start']['date'] ?? null;
                $endDate = $event['end']['date'] ?? null;
                if ($startDate) {
                    $start = new \DateTimeImmutable($startDate);
                    // Google all-day events use exclusive end date
                    $end = new \DateTimeImmutable(($endDate ?: $startDate));
                    $endExclusive = $end;
                    for ($d = $start; $d < $endExclusive; $d = $d->modify('+1 day')) {
                        $holidayDates[$d->format('Y-m-d')] = true;
                    }
                } else {
                    // Timed event; treat its date as holiday date (rare for public holiday calendars)
                    $startDateTime = $event['start']['dateTime'] ?? null;
                    if ($startDateTime) {
                        $holidayDates[(new \DateTimeImmutable($startDateTime))->format('Y-m-d')] = true;
                    }
                }
            }
            return array_keys($holidayDates);
        });

        return in_array($date->format('Y-m-d'), $dates, true);
    }
}


