<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use wataridori\ChatworkSDK\ChatworkSDK;
use wataridori\ChatworkSDK\ChatworkRoom;

class ChatworkAwesomeDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chatwork:send-awesome-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder for all member in ChimLon group';

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
        ChatworkSDK::setApiKey(env('CHATWORK_AWESOME_API_KEY'));
        $room = new ChatworkRoom(env('CHATWORK_AWESOME_ID'));
        $room->sendMessage("[To:2619939][To:1858025][To:1991503][To:2047133][To:2118814][To:2357269][To:1492255][To:2076387][To:2771325]\nCác đồng chí hoàn thành Daily Report nhé!");
    }
}
