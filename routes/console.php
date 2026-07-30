<?php

use App\Console\Commands\ImportSqliteToMysqlCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::starting(function ($artisan): void {
    $artisan->resolveCommands([
        ImportSqliteToMysqlCommand::class,
    ]);
});

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
