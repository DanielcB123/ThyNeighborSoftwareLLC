<?php

use App\Demo\Commands\DemoCredentialsCommand;
use App\Demo\Commands\DemoRefreshOperationsCommand;
use App\Demo\Commands\DemoResetCommand;
use App\Demo\Commands\DemoSeedCommand;
use App\Demo\Commands\DemoVerifyCommand;
use App\Console\Commands\ImportSqliteToMysqlCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::addCommands([
    ImportSqliteToMysqlCommand::class,
    DemoSeedCommand::class,
    DemoResetCommand::class,
    DemoCredentialsCommand::class,
    DemoVerifyCommand::class,
    DemoRefreshOperationsCommand::class,
]);

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
