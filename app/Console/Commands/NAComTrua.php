<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use wataridori\ChatworkSDK\ChatworkRoom;
use wataridori\ChatworkSDK\ChatworkSDK;

class NAComTrua extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chatwork:send-daily-lunch-to-na';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder to NA';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ChatworkSDK::setApiKey(env('CHATWORK_MAIN_API_KEY'));
        $room = new ChatworkRoom(env('CHATWORK_NA_ID'));
        $room->sendMessage("[To:1887556] Ăn vạ\nĐói rồi, cơm thôi\n(an)");
    }
}
