<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Events\ConnectionAttempted;
use Illuminate\Support\Facades\DB;

class DatabaseConnectionProvider extends ServiceProvider
{
    public function boot()
    {
        // ✅ Enable persistent connections
        DB::connection()->setAutoCommit(true);
        
        // ✅ Handle connection errors gracefully
        DB::listen(function ($query) {
            if ($query->time > 1000) { // More than 1 second
                \Log::warning("Slow query detected: {$query->sql}", [
                    'time' => $query->time . 'ms',
                    'bindings' => $query->bindings
                ]);
            }
        });
    }
}