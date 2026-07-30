<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;

class CentralSetting extends CentralModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
    ];
}
