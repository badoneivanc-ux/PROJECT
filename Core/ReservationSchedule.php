<?php

namespace Project\Core;

class ReservationSchedule
{
    public const SESSIONS = [
        'matin' => ['08:30', '09:00', '09:30'],
        'apres_midi' => ['13:30', '14:00', '14:30'],
    ];
    public const CRENEAUX = ['08:30', '09:00', '09:30', '13:30', '14:00', '14:30'];

    public static function sessionForTime(string $time): string
    {
        foreach (self::SESSIONS as $session => $slots) {
            if (in_array(substr($time, 0, 5), $slots, true)) {
                return $session;
            }
        }
        throw new \InvalidArgumentException('Créneau de réservation invalide.');
    }

    public static function slotsForSession(string $session): array
    {
        if (!isset(self::SESSIONS[$session])) {
            throw new \InvalidArgumentException('Séance invalide.');
        }
        return self::SESSIONS[$session];
    }
}
