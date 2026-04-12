<?php

/**
 * Global Helper Functions
 */

use App\Helpers\QuestionHelper;

if (! function_exists('getQuestionTypeLabel')) {
    /**
     * Get human-readable label for question type
     *
     * @param string $tipe
     * @return string
     */
    function getQuestionTypeLabel(string $tipe): string
    {
        return QuestionHelper::getTypeLabel($tipe);
    }
}

if (! function_exists('getQuestionTypeOptions')) {
    /**
     * Get all question type options with labels
     *
     * @return array
     */
    function getQuestionTypeOptions(): array
    {
        return QuestionHelper::getTypeOptions();
    }
}

if (! function_exists('questionRequiresOptions')) {
    /**
     * Check if question type requires options
     *
     * @param string $tipe
     * @return bool
     */
    function questionRequiresOptions(string $tipe): bool
    {
        return QuestionHelper::requiresOptions($tipe);
    }
}

if (! function_exists('questionRequiresRange')) {
    /**
     * Check if question type requires min/max range
     *
     * @param string $tipe
     * @return bool
     */
    function questionRequiresRange(string $tipe): bool
    {
        return QuestionHelper::requiresRange($tipe);
    }
}
