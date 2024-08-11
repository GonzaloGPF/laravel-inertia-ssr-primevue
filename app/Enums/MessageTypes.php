<?php

namespace App\Enums;

enum MessageTypes: string
{
    case SUCCESS = 'success';
    case INFO = 'info';
    case WARNING = 'warning';
    case DANGER = 'danger';
}
