<?php
// -shaine: Registers command-line routes for the application.

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

// -shaine: Provides Laravel's sample inspirational command.
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
