<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use wataridori\ChatworkSDK\ChatworkSDK;
use wataridori\ChatworkSDK\ChatworkRoom;

class ChatworkTinhTinh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chatwork:tinh-tinh';

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
        ChatworkSDK::setApiKey(env('CHATWORK_API_KEY'));
        $room = new ChatworkRoom(env('CHATWORK_CHIMLON_ID'));
        $room->sendMessage("[toall] Tình hình là cuối ngày hôm nay lương sẽ về, mọi người chuẩn bị ăn chơi nào!\n(dance7)");
    }
}
