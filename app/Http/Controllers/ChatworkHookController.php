<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Cmfcmf\OpenWeatherMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use JeroenG\Flickr\Api;
use JeroenG\Flickr\Flickr;
use wataridori\ChatworkSDK\ChatworkRoom;
use wataridori\ChatworkSDK\ChatworkSDK;

class ChatworkHookController extends Controller
{
    private const OTHER = 0;
    private const TAT = 1;
    private const SONGPHI = 2;
    private const CUT = 3;
    private const DAP = 4;
    private const DANCE = 5;
    private const FACE = 6;
    private const NGON = 7;
    private const CLAP = 8;
    private const VA = 8;
    private const DANH_NHAU = 9;
    private const BOX_XING = 10;
    private const VIETLOT = 13;


    private const WEATHER_TODAY = 11;
    private const WEATHER_OTHER = 12;
    private const FLICKR = 20;
    private const TUMBLR = 21;
    private const PER_PAGE = 1;


    private const FLICKR_CAT = 100;
    private const FLICKR_ZAI = 101;
    private const FLICKR_HARD_CORE = 102;
    //private const FLICKR_CAT = 103;

    /**
     * @param Request $request
     */
    public function chatworkHook(Request $request)
    {
        ChatworkSDK::setApiKey(env('CHATWORK_API_KEY'));

        $webhook = $request->get('webhook_event');
        
        // Open for all user
        //if ($webhook["room_id"] == env('CHATWORK_CHIMLON_ID')) {
            $fromMessage = strtolower($webhook['body']);
            $type = $this->getMessageType($fromMessage);
            $message = $this->buildMessage($type, $fromMessage);

            $reply = "[rp aid=" . $webhook['from_account_id'] . " to=" . $webhook['room_id'] . "-" . $webhook['message_id'] . "]\n";
            $room = new ChatworkRoom($webhook["room_id"]);
            $room->sendMessage($reply . $message);
        //}
    }

    /**
     * @param int $type
     * @param string $fromMessage
     * @return string
     */
    private function buildMessage(int $type, string $fromMessage): string {
        switch ($type) {
            case self::TAT:
                return $this->getRamdomTatEmo();
            case self::SONGPHI:
                return "(songphi)";
            case self::CUT:
                return "(cut)";
            case self::DAP:
                return "(dap2)";
            case self::DANCE:
                return $this->getRamdomDance();
            case self::FACE:
                return "(facepalm)";
            case self::NGON:
                return "(ngon)";
            case self::CLAP:
                return "(clap)";
            case self::VA:
                return "(va)";
            case self::DANH_NHAU:
                return "(danhnhau)";
            case self::BOX_XING:
                return "(boxing)";
            case self::VIETLOT:
                return $this->getRamdomVietlot();
            case self::WEATHER_TODAY:
                return $this->getTodayWeather();
            case self::WEATHER_OTHER:
                return $this->getOtherWeather($fromMessage);
            case self::FLICKR:
                return $this->getFlickrImages($fromMessage);
        }

        return $this->getRamdomEmo();
    }

    /**
     * @return string
     */
    private function getTodayWeather(): string {
        try {
            $weather = new OpenWeatherMap(env('OPEN_WEATHER_API'));
            $data = $weather->getWeather('Hanoi', "metric", "vi");
            $nd = str_replace("&deg;", "", $data->temperature->getFormatted());

            $message = " ・ Nhiệt độ hiện tại: " . $nd .
                "\n ・ Thời tiết: " . $data->weather->description;
            return $message;
        } catch (\Exception $e) {
            return "Something went wrong!";
        }
    }

    /**
     * @param string $from
     * @return string
     */
    private function getOtherWeather(string $from): string {
        try {
            $weather = new OpenWeatherMap(env('OPEN_WEATHER_API'));
            $forecast = $weather->getRawHourlyForecastData('Hanoi', "imperial", "vi", "", "json");

            $result = [];
            $data = json_decode($forecast, true);
            for ($i  = 0; $i < $data['cnt']; $i++) {
                $value = $data["list"][$i];
                $date = Carbon::parse($value['dt_txt'])->format('d-m-Y');
                $dateData = [];
                if (array_key_exists($date, $result)) {
                    $dateData = $result[$date];
                    $dateData['max'] = $dateData['max'] < $value['main']['temp_max'] ? $value['main']['temp_max'] : $dateData['max'];
                    $dateData['min'] = $dateData['min'] > $value['main']['temp_min'] ? $value['main']['temp_min'] : $dateData['min'];
                    $dateData['type'][] = $value['weather'][0]['description'];
                } else {
                    $dateData['max'] = $value['main']['temp_max'];
                    $dateData['min'] = $value['main']['temp_min'];
                    $dateData['type'] = [$value['weather'][0]['description']];
                }

                $result[$date] = $dateData;
            }

            $message = "Thời tiết 3 ngày tới:";
            $result = array_slice($result, 0, 3);
            foreach ($result as $day => $weather) {
                $message = $message . "\nNgày: " . $day . ":";
                $message = $message . "\n    ・ Nhiệt độ cao nhất: " . $this->fahrenheit_to_celsius($weather['max']);
                $message = $message . "\n    ・ Nhiệt độ thấp nhất: " . $this->fahrenheit_to_celsius($weather['min']);
                $message = $message . "\n    ・ Thời tiết: " . $this->getType($weather['type']);
            }

            return $message;
        } catch (\Exception $e) {
        }

        return "In progress...";
    }

    //Fahrenheit to celsius
    function fahrenheit_to_celsius($given_value)
    {
        $celsius = 5 / 9 * ($given_value - 32);
        return (int) $celsius;
    }

    private function getType(array $types): String {
        return implode(", ", array_unique($types));
    }

    /**
     * @param string $from
     * @return int
     */
    private function getMessageType(string $from): int {
        if (strpos($from, "thời tiết") > 0) {
            if (strpos($from, "hôm nay")) {
                return self::WEATHER_TODAY;
            }

            return self::WEATHER_OTHER;
        } else if (strpos($from, "tat") > 0) {
            return self::TAT;
        } else if (strpos($from, "cut") > 0) {
            return self::CUT;
        } else if (strpos($from, "songphi") > 0) {
            return self::SONGPHI;
        } else if (strpos($from, "dap") > 0) {
            return self::DAP;
        } else if (strpos($from, "dance") > 0) {
            return self::DANCE;
        } else if (strpos($from, "facepalm") > 0) {
            return self::FACE;
        } else if (strpos($from, "ngon") > 0) {
            return self::NGON;
        } else if (strpos($from, "clap") > 0) {
            return self::CLAP;
        } else if (strpos($from, "va") > 0) {
            return self::VA;
        } else if (strpos($from, "danhnhau") > 0) {
            return self::DANH_NHAU;
        } else if (strpos($from, "boxing") > 0) {
            return self::BOX_XING;
        } else if (strpos($from, "vietlot") > 0) {
            return self::VIETLOT;
        } else if (strpos($from, "flickr") > 0) {
            return self::FLICKR;
        }

        return self::OTHER;
    }

        
    private function getRamdomEmo(): string {
        $emoticons = ["(bad-luck)","(ballerina)","(blush)","(booger)","(boohoo)","(boring)","(bye)","(calm-down)","(dance-with-me)","(dancing)","(dead)","(dizzy)","(drama)","(dunno)","(eating)","(exorcist)","(floating)","(flying)","(get-out)","(happiness)","(happy-birthday)","(happy-dancing)","(hate)","(head)","(help)","(kill)","(kill-me)","(kill-myself)","(looser)","(love-food)","(love-you)","(magic-left)","(magic-right)","(moving)","(music)","(nah)","(no-no)","(on-jail)","(patpat-dance)","(pheew)","(poking)","(scratching)","(shake)","(sigh)","(sleeping)","(smooth)","(snack)","(stars)","(taichi)","(teehee)","(up)","(up-down)","(what)","(yeah)","(yes-sir)","(yesss)","(yipee)"];

        return $emoticons[array_rand($emoticons)];
    }

    private function getRamdomTatEmo(): string {
        $emoticons = ["(2tat)", "(tat)", "(tat2)", "(tat3)", "(tat4)", "(tat5)", "(tat6)", "(tat7)", "(tattrom)", "(tattrom2)", "(pettat)", "(pettat2)", "(pettat3)", "(pettat4)", "(pettat5)"];

        return $emoticons[array_rand($emoticons)];
    }

    private function getRamdomVietlot(): string {
        $message = "RAMDOM VIETLOT: " .
            str_pad(rand(0, 99), 2, "0", STR_PAD_LEFT) . " - " .
            str_pad(rand(0, 99), 2, "0", STR_PAD_LEFT) . " - " .
            str_pad(rand(0, 99), 2, "0", STR_PAD_LEFT) . " - " .
            str_pad(rand(0, 99), 2, "0", STR_PAD_LEFT) . " - " .
            str_pad(rand(0, 99), 2, "0", STR_PAD_LEFT) . " - " .
            str_pad(rand(0, 99), 2, "0", STR_PAD_LEFT) .
            "\nChúc bạn may mắn (tanghoa)";

        return $message;
    }

    private function getRamdomDance(): string {
        $emoticons = ["(dance-with-me)", "(patpat-dance)", "(dance9)", "(dance8)", "(dance7)", "(dance6)", "(dance5)", "(dance4)", "(dance3)", "(dance2)"];
        return $emoticons[array_rand($emoticons)];
    }

    private function getFlickrImages(string $message = "") {
        $urls = [];
        $images = [];

        if (strpos($message, "flickr:cat") != -1) {
            $urls = $this->getCatImages();
            preg_match_all("/flickr\ ?:\ ?cat\ ?:\ ?[0-9]+\ ?/", $message,$matches);
        } else {
            $urls = $this->getRandomImages();
            preg_match_all("/flickr\ ?:\ ?[0-9]+\ ?/", $message,$matches);
        }

        shuffle($urls);
        if (count($matches[0])) {
            $extract = $matches[0][0];
            preg_match_all("/[0-9]+/", $extract, $perPage);
            if (count($perPage[0])) {
                $count = $perPage[0][0];
                $count = $count > env('FLICKR_COUNT') ? env('FLICKR_COUNT') : $count;
                $images = array_slice($urls, 0, $count);
            } else {
                $images = array_slice($urls, 0, self::PER_PAGE);
            }
        } else {
            $images = array_slice($urls, 0, self::PER_PAGE);
        }

        return "[NSFW] Random flickr images: \n" . implode("\n", $images);
    }

    private function getCatImages(int $perPage = 0) {
        $flickr = new Flickr(new Api(env('FLICKR_API_KEY')));
        $count = $perPage ?: env('FLICKR_COUNT');
        $images = $flickr->request(
            "flickr.photos.search",
            ['text' => 'cat', 'count' => $count, 'page' => rand(1, 20)]
        );

        return $this->parseFlickrImages($images);
    }

    /**
     * @param int $perPage
     * @return array
     */
    private function getRandomImages(int $perPage = 0) {
        $flickr = new Flickr(new Api(env('FLICKR_API_KEY')));
        $count = $perPage ?: env('FLICKR_COUNT');
        $images = $flickr->request(
            "flickr.photos.getContactsPublicPhotos",
            ['user_id' => env('FLICKR_ACCOUNT_ID'), 'count' => $count, 'page' => rand(1, 20)]
        );

        return $this->parseFlickrImages($images);
    }

    private function parseFlickrImages($images) {
        $photos = $images->photos['photo'];
        $urls = [];
        for ($i = 0; $i < count($photos); $i ++) {
            $photo = $photos[$i];
            $farm = (int) $photo['farm'];
            $urls[] = "https://farm{$farm}.staticflickr.com/{$photo['server']}/{$photo['id']}_{$photo['secret']}_h.jpg";
        }

        return $urls;
    }
}
