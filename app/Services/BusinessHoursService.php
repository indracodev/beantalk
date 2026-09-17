<?php

namespace App\Services;

use App\Models\WidgetSetting;
use Carbon\Carbon;

class BusinessHoursService
{
    /**
     * Day-of-week map: Carbon dayOfWeek (0=Sunday) -> JSON key
     */
    private const DAY_MAP = [
        0 => 'sun',
        1 => 'mon',
        2 => 'tue',
        3 => 'wed',
        4 => 'thu',
        5 => 'fri',
        6 => 'sat',
    ];

    /**
     * Checks if the current time is within configured business hours.
     * Evaluation is based on the admin's configured timezone (business_hours_timezone),
     * NOT the visitor's local timezone — because business hours represent CS team availability.
     */
    public function isWithinBusinessHours(WidgetSetting $ws): bool
    {
        if (!$ws->business_hours_enabled) {
            return true; // Feature disabled = always available
        }

        $schedule = $ws->business_hours;
        if (empty($schedule) || !is_array($schedule)) {
            return true; // No schedule configured = always available
        }

        $tz = $ws->business_hours_timezone ?: 'Asia/Jakarta';

        try {
            $now = Carbon::now($tz);
        } catch (\Exception $e) {
            $now = Carbon::now('Asia/Jakarta');
        }

        // 1. Check holidays first
        if ($this->isHoliday($ws, $now)) {
            return false;
        }

        // 2. Check day schedule
        $dayKey = self::DAY_MAP[$now->dayOfWeek] ?? null;
        if (!$dayKey || !isset($schedule[$dayKey])) {
            return false;
        }

        $dayConfig = $schedule[$dayKey];
        if (empty($dayConfig['enabled'])) {
            return false;
        }

        $start = $dayConfig['start'] ?? null;
        $end = $dayConfig['end'] ?? null;
        if (!$start || !$end) {
            return false;
        }

        $currentTime = $now->format('H:i');
        return $currentTime >= $start && $currentTime < $end;
    }

    /**
     * Checks if a given date falls on a configured holiday.
     */
    public function isHoliday(WidgetSetting $ws, ?Carbon $date = null): bool
    {
        $holidays = $ws->holidays;
        if (empty($holidays) || !is_array($holidays)) {
            return false;
        }

        $tz = $ws->business_hours_timezone ?: 'Asia/Jakarta';
        if (!$date) {
            $date = Carbon::now($tz);
        }

        $todayStr = $date->format('Y-m-d');

        foreach ($holidays as $holiday) {
            if (isset($holiday['date']) && $holiday['date'] === $todayStr) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the offline message text — either custom or a sensible default.
     */
    public function getOfflineMessage(WidgetSetting $ws): string
    {
        if (!empty($ws->business_hours_off_message)) {
            return $ws->business_hours_off_message;
        }

        $lang = $ws->language ?: 'id';
        if ($lang === 'en') {
            return 'We are currently outside business hours. Your message has been received and will be replied to via email.';
        }

        return 'Saat ini di luar jam kerja. Pesan Anda tetap kami terima dan akan dibalas via email.';
    }

    /**
     * Returns the full business hours schedule for client-side display.
     */
    public function getScheduleSummary(WidgetSetting $ws): array
    {
        return [
            'enabled'     => (bool) $ws->business_hours_enabled,
            'schedule'    => $ws->business_hours ?: [],
            'timezone'    => $ws->business_hours_timezone ?: 'Asia/Jakarta',
            'holidays'    => $ws->holidays ?: [],
            'off_message' => $this->getOfflineMessage($ws),
        ];
    }
}
