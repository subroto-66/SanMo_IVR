<?php

namespace App\Models;

use App\Services\AudioStorageService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IvrOption extends Model
{
    use HasFactory;

    protected $table = 'ivr_options';

    protected $fillable = [
        'digit',
        'title',
        'description',
        'audio_path',
        'audio_url',
        'action_type',
        'sms_enabled',
        'sms_message',
        'fallback_text',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sms_enabled' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope active options.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordered options.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('digit', 'asc');
    }

    /**
     * Determine if this option has audio configured.
     */
    public function hasAudio(): bool
    {
        return !empty($this->audio_path) || !empty($this->audio_url);
    }

    /**
     * Get resolved audio URL for playback.
     */
    public function getResolvedAudioUrlAttribute(): ?string
    {
        if (!empty($this->audio_path)) {
            return app(AudioStorageService::class)->getUrl($this->audio_path);
        }

        if (!empty($this->audio_url)) {
            return $this->audio_url;
        }

        return null;
    }

    /**
     * Get human-readable backend activity label for this option.
     */
    public function getActivityLabelAttribute(): string
    {
        return match ($this->digit) {
            '1' => '1 — Learn FlowCore',
            '2' => '2 — Presentation',
            '3' => '3 — Participation/Compensation',
            '4' => '4 — Next Steps',
            '5' => '5 — Contact Request',
            '0' => '0 — Return to Main Menu',
            '#' => '# — End Call',
            default => "{$this->digit} — {$this->title}",
        };
    }

    /**
     * Extract destination URL from sms_message if present.
     */
    public function getDestinationUrlAttribute(): ?string
    {
        if (empty($this->sms_message)) {
            return null;
        }

        if (preg_match('/(https?:\/\/[^\s]+|DiscoverFlowCore\.com\/[^\s]+)/i', $this->sms_message, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
