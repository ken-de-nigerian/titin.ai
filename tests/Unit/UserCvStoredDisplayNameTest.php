<?php

declare(strict_types=1);

use App\Support\UserCvStoredDisplayName;

it('appends a random suffix before the extension', function () {
    $result = UserCvStoredDisplayName::uniqueFromClientName('Resume.pdf');

    expect($result)->toMatch('/^Resume_[a-z0-9]{8}\.pdf$/');
});

it('handles empty input with a generated name', function () {
    $result = UserCvStoredDisplayName::uniqueFromClientName('   ');

    expect($result)->toMatch('/^cv_[a-z0-9]{10}\.pdf$/');
});

it('produces different strings on successive calls for the same input', function () {
    $a = UserCvStoredDisplayName::uniqueFromClientName('same.docx');
    $b = UserCvStoredDisplayName::uniqueFromClientName('same.docx');

    expect($a)->not->toBe($b);
});
