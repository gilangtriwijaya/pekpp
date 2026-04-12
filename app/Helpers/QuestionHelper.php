<?php

namespace App\Helpers;

class QuestionHelper
{
    public static function getTypeLabel(string $tipe): string
    {
        $labels = [
            'text' => 'Teks Pendek',
            'textarea' => 'Teks Panjang',
            'number' => 'Angka',
            'radio' => 'Pilihan Ganda',
            'checkbox' => 'Pilihan Banyak',
            'select' => 'Dropdown',
            'yesno' => 'Ya/Tidak',
            'skala' => 'Skala'
        ];

        return $labels[$tipe] ?? $tipe;
    }

    public static function getTypeOptions(): array
    {
        return [
            'text' => '📝 Teks Pendek (Short Text)',
            'textarea' => '📄 Teks Panjang (Long Text)',
            'number' => '🔢 Angka (Numeric)',
            'radio' => '⭕ Pilihan Ganda (Multiple Choice)',
            'checkbox' => '☑️ Pilihan Banyak (Multiple Select)',
            'select' => '📋 Dropdown (Pilihan Tunggal)',
            'yesno' => '✅ Ya/Tidak (Yes/No)',
            'skala' => '📊 Skala (Rating Scale)'
        ];
    }

    public static function requiresOptions(string $tipe): bool
    {
        return in_array($tipe, ['radio', 'checkbox', 'select']);
    }

    public static function requiresRange(string $tipe): bool
    {
        return in_array($tipe, ['number', 'skala']);
    }
}
