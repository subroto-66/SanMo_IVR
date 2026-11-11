<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IvrCallLog extends Model
{
    use HasFactory;

    protected $table = 'ivr_call_logs';

    protected $fillable = [
        'call_sid',
        'from_number',
        'to_number',
        'direction',
        'status',
        'selected_option',
        'duration',
        'started_at',
        'ended_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration' => 'integer',
    ];

    /**
     * Record an event in the call's metadata audit trail.
     */
    public function recordEvent(string $event, array $data = []): void
    {
        $metadata = $this->metadata ?? [];
        $events = $metadata['events'] ?? [];

        $events[] = [
            'event' => $event,
            'time' => Carbon::now()->toIso8601String(),
            'data' => $data,
        ];

        $metadata['events'] = $events;
        $this->metadata = $metadata;
        $this->save();
    }

    /**
     * Get a human-readable duration string.
     */
    public function getFormattedDurationAttribute(): string
    {
        if ($this->duration === null) {
            return '-';
        }

        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;

        if ($minutes > 0) {
            return sprintf('%dm %02ds', $minutes, $seconds);
        }

        return sprintf('%ds', $seconds);
    }

    /**
     * Check if this call session requested personal contact.
     */
    public function hasContactRequest(): bool
    {
        return !empty($this->metadata['has_contact_request'])
            || (is_string($this->selected_option) && str_contains($this->selected_option, 'Contact Request'));
    }

    /**
     * Append a human-readable activity to the caller's selection sequence.
     */
    public function appendActivity(string $label, array $extra = []): void
    {
        $current = $this->selected_option;
        if (empty($current)) {
            $this->selected_option = $label;
        } else {
            $parts = explode(' → ', $current);
            if (end($parts) !== $label) {
                $newSelected = $current . ' → ' . $label;
                if (strlen($newSelected) > 250) {
                    $newSelected = '...' . substr($newSelected, -240);
                }
                $this->selected_option = $newSelected;
            }
        }

        $metadata = $this->metadata ?? [];
        $selections = $metadata['selections'] ?? [];
        $selections[] = array_merge([
            'label' => $label,
            'time' => Carbon::now()->toIso8601String(),
        ], $extra);

        $metadata['selections'] = $selections;
        $this->metadata = $metadata;
        $this->save();
    }

    /**
     * Get array of sequential activity steps.
     *
     * @return array<int, array{digit: ?string, label: string, raw: string}>
     */
    public function getSelectionStepsAttribute(): array
    {
        if (empty($this->selected_option)) {
            return [];
        }

        $items = array_map('trim', explode(' → ', $this->selected_option));
        $structured = [];

        foreach ($items as $raw) {
            $digit = null;
            $label = $raw;
            if (preg_match('/^(\S+)\s*[—\-]\s*(.+)$/u', $raw, $m)) {
                $digit = $m[1];
                $label = $m[2];
            }

            $structured[] = [
                'digit' => $digit,
                'label' => $label,
                'raw' => $raw,
            ];
        }

        return $structured;
    }
}
