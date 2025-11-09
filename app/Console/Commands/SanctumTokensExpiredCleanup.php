<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class SanctumTokensExpiredCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:expired-cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Очистка просроченных Sanctum токенов';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();
        $deletedExpiredTokens = PersonalAccessToken::where('expires_at', '<', $now)->delete();

        $this->info("Удалено {$deletedExpiredTokens} просроченных токена(ов)",);
        Log::channel('tokens_single')->info("Удалено {$deletedExpiredTokens} просроченных токена(ов) в {$now}");
    }
}
