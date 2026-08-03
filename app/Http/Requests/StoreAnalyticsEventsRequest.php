<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnalyticsEventsRequest extends FormRequest
{
    public function authorize(): bool { return (bool) config('analytics.enabled'); }
    public function rules(): array
    {
        return [
            'session_id' => ['required','uuid'], 'visitor_id' => ['required','string','max:128'],
            'events' => ['required','array','min:1','max:20'], 'events.*.id' => ['required','uuid'],
            'events.*.name' => ['required',Rule::in(config('analytics.allowed_events'))],
            'events.*.path' => ['nullable','string','max:512','not_regex:/[<>]/'],
            'events.*.timestamp' => ['nullable','date'], 'events.*.properties' => ['nullable','array','max:20'],
            'events.*.properties.*' => ['nullable'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (strlen($this->getContent()) > 65536) abort(413, 'Payload demasiado grande.');
    }
}
