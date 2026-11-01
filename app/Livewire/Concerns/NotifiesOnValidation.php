<?php

namespace App\Livewire\Concerns;

use Illuminate\Validation\ValidationException;

/**
 * Shared behaviour for every panel form: when a save/submit fails
 * validation, the user must ALWAYS get an explicit notification
 * (toast) telling them which part of the form is the problem —
 * never a silent «nothing happened».
 *
 * Usage:
 *   $this->validateWithToast(fn () => $this->validate($rules, $messages));
 *   // or wrap any callable that internally calls validate()
 */
trait NotifiesOnValidation
{
    /**
     * Run a validator callable; on ValidationException dispatch the
     * global `panel-toast` event with the first error message (plus
     * the total error count), then re-throw so Livewire still renders
     * the per-field errors inline.
     *
     * @throws ValidationException
     */
    protected function validateWithToast(callable $validator, string $prefix = 'لطفاً خطاهای فرم را برطرف کنید'): void
    {
        try {
            $validator();
        } catch (ValidationException $exception) {
            $messages = collect($exception->errors())
                ->flatten()
                ->filter(fn ($message) => trim((string) $message) !== '')
                ->values();

            $first = $messages->first();

            $this->dispatch('panel-toast', message: $first
                ? $first
                : $prefix.'؛ موارد مشخص‌شده را تکمیل کنید.');

            throw $exception;
        }
    }
}
