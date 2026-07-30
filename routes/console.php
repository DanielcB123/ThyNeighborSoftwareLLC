<?php

use App\Console\Commands\ImportSqliteToMysqlCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::addCommands([
    ImportSqliteToMysqlCommand::class,
]);

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
