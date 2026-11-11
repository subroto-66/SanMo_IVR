<?php

use App\Models\IvrCallLog;
use App\Models\IvrOption;

test('IvrCallLog formats seconds into human readable duration', function () {
    $log = new IvrCallLog(['duration' => 45]);
    expect($log->formatted_duration)->toBe('45s');

    $log2 = new IvrCallLog(['duration' => 125]);
    expect($log2->formatted_duration)->toBe('2m 05s');

    $log3 = new IvrCallLog(['duration' => null]);
    expect($log3->formatted_duration)->toBe('-');
});

test('IvrOption detects whether audio is configured', function () {
    $optionWithoutAudio = new IvrOption([
        'digit' => '1',
        'title' => 'Test',
        'audio_path' => null,
        'audio_url' => null,
    ]);
    expect($optionWithoutAudio->hasAudio())->toBeFalse();

    $optionWithAudio = new IvrOption([
        'digit' => '2',
        'title' => 'Test 2',
        'audio_path' => 'ivr-options/test.mp3',
    ]);
    expect($optionWithAudio->hasAudio())->toBeTrue();
});
