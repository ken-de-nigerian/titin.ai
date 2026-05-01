<?php

declare(strict_types=1);

namespace App\Enums;

enum UserCvStatus: string
{
    case Uploaded = 'uploaded';
    case Processing = 'processing';
    case Parsed = 'parsed';
    case Failed = 'failed';
    case Deleted = 'deleted';
}
