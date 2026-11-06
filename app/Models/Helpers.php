<?php

namespace App\Models;

class Helpers
{
    static function href($path, $class = '')
    {
        if (request()->path() == $path) {
            $class .= '';
        }

        return 'class="' . $class . '" href="' . url($path) . '"';
    }

    static function linkify($value, $protocols = array('http', 'mail'), array $attributes = array())
    {
        // Link attributes
        $attr = '';
        foreach ($attributes as $key => $val) {
            $attr .= ' ' . $key . '="' . htmlentities($val) . '"';
        }

        $links = array();

        // Extract existing links and tags
        $value = preg_replace_callback('~(<a .*?>.*?</a>|<.*?>)~i', function ($match) use (&$links) {
            return '<' . array_push($links, $match[1]) . '>';
        }, $value);

        // Extract text links for each protocol
        foreach ((array) $protocols as $protocol) {
            switch ($protocol) {
                case 'http':
                case 'https':
                    $value = preg_replace_callback('~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) {
                        if ($match[1])
                            $protocol = $match[1];
                        $link = $match[2] ?: $match[3];
                        return '<' . array_push($links, "<a target='blank' $attr href=\"$protocol://$link\">$link</a>") . '>';
                    }, $value);
                    break;
                case 'mail':
                    $value = preg_replace_callback('~([^\s<]+?@[^\s<]+?\.[^\s<]+)(?<![\.,:])~', function ($match) use (&$links, $attr) {
                        return '<' . array_push($links, "<a $attr href=\"mailto:{$match[1]}\">{$match[1]}</a>") . '>';
                    }, $value);
                    break;
                case 'twitter':
                    $value = preg_replace_callback('~(?<!\w)[@#](\w++)~', function ($match) use (&$links, $attr) {
                        return '<' . array_push($links, "<a target='blank' $attr href=\"https://twitter.com/" . ($match[0][0] == '@' ? '' : 'search/%23') . $match[1] . "\">{$match[0]}</a>") . '>';
                    }, $value);
                    break;
                default:
                    $value = preg_replace_callback('~' . preg_quote($protocol, '~') . '://([^\s<]+?)(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) {
                        return '<' . array_push($links, "<a target='blank' $attr href=\"$protocol://{$match[1]}\">{$match[1]}</a>") . '>';
                    }, $value);
                    break;
            }
        }

        // Insert all link
        return preg_replace_callback('/<(\d+)>/', function ($match) use (&$links) {
            return $links[$match[1] - 1];
        }, $value);
    }

    static function can($action, $model)
    {
        if (!auth()->user()) {
            return false;
        }

        return auth()->user()->can($action, $model);
    }

    public static $currencies = [
        [
            'code' => 'USD',
            'symbol' => '$',
            'zero_decimal' => false,
        ],
        [
            'code' => 'ARS',
            'symbol' => '$',
            'zero_decimal' => false,
        ],
        [
            'code' => 'AED',
            'symbol' => 'د.إ',
            'zero_decimal' => false,
        ],
        [
            'code' => 'AUD',
            'symbol' => 'A$',
            'zero_decimal' => false,
        ],
        [
            'code' => 'EUR',
            'symbol' => '€',
            'zero_decimal' => false,
        ],
        [
            'code' => 'SEK',
            'symbol' => 'kr',
            'zero_decimal' => false,
        ],
        [
            'code' => 'NOK',
            'symbol' => 'kr',
            'zero_decimal' => false,
        ],
        [
            'code' => 'DKK',
            'symbol' => 'Kr',
            'zero_decimal' => false,
        ],
        [
            'code' => 'CHF',
            'symbol' => 'SFr',
            'zero_decimal' => false,
        ],
        [
            'code' => 'GBP',
            'symbol' => '£',
            'zero_decimal' => false,
        ],
        [
            'code' => 'BRL',
            'symbol' => 'R$',
            'zero_decimal' => false,
        ],
        [
            'code' => 'CAD',
            'symbol' => 'C$',
            'zero_decimal' => false,
        ],
        [
            'code' => 'HKD',
            'symbol' => 'HK$',
            'zero_decimal' => false,
        ],
        [
            'code' => 'INR',
            'symbol' => '₹',
            'zero_decimal' => false,
        ],
        [
            'code' => 'MXN',
            'symbol' => 'Mex$',
            'zero_decimal' => false,
        ],
        [
            'code' => 'NZD',
            'symbol' => '$',
            'zero_decimal' => false,
        ],
        [
            'code' => 'SGD',
            'symbol' => 'S$',
            'zero_decimal' => false,
        ],
        [
            'code' => 'PEN',
            'symbol' => 'S/',
            'zero_decimal' => false,
        ],
        [
            'code' => 'PYG',
            'symbol' => '₲',
            'zero_decimal' => true,
        ],
        [
            'code' => 'ZAR',
            'symbol' => 'R',
            'zero_decimal' => false,
        ],
        [
            'code' => 'XAF',
            'symbol' => 'F.CFA',
            'zero_decimal' => false,
        ],
        [
            'code' => 'NGN',
            'symbol' => '₦',
            'zero_decimal' => false,
        ],
    ];

    static function getSymbol($code)
    {
        $index = array_search($code, array_column(self::$currencies, 'code'));
        return self::$currencies[$index]['symbol'];
    }

    static function getCode($symbol)
    {
        $index = array_search($symbol, array_column(self::$currencies, 'symbol'));
        return self::$currencies[$index]['code'];
    }

    static function getCurrency($code)
    {
        $index = array_search($code, array_column(self::$currencies, 'code'));
        return self::$currencies[$index];
    }

    static function getCurrencyRate($currency)
    {
        $rate = CurrencyRate::where('currency', $currency)->first();
        if ($rate) {
            return 1 / $rate->rate;
        }
        return 1;
    }

    static function createLiveStream($name)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get(env('AGORA_BACKEND_URL') . '/v1/login?project_id=' . env('AGORA_LIVESTREAM_PROJECT_ID') . '&platform_id=turnkey_web');
            $token = \json_decode($response->getBody()->getContents())->token;
            $response = $client->post(env('AGORA_BACKEND_URL') . '/v1/query', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                ],
                'body' => json_encode([
                    'operationName' => 'CreateChannel',
                    'variables' => [
                        'title' => $name,
                        'enablePSTN' => false
                    ],
                    'query' => 'mutation CreateChannel($title: String!, $enablePSTN: Boolean) {
                                createChannel(title: $title, enablePSTN: $enablePSTN) {
                                    passphrase {
                                        host
                                        view
                                        __typename
                                    }
                                    channel
                                    title
                                    pstn {
                                        number
                                        dtmf
                                        __typename
                                    }
                                    __typename
                                }
                            }',
                ])
            ]);
            $response = \json_decode($response->getBody()->getContents());
            return [
                'host' => $response->data->createChannel->passphrase->host,
                'view' => $response->data->createChannel->passphrase->view
            ];
        } catch (\Throwable $th) {
            return null;
        }
    }

    static function createCloudflareLiveInput($name)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://api.cloudflare.com/client/v4/accounts/' . env('CLOUDFLARE_ACCOUNT_ID') . '/stream/live_inputs', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '. env('CLOUDFLARE_TOKEN')
                ],
                'body' => json_encode([
                    'meta' => [
                        'name' => $name
                    ],
                    'recording' => [
                        'mode' => 'automatic'
                    ],
                    'deleteRecordingAfterDays' => null
                ])
            ]);
            $response = \json_decode($response->getBody()->getContents());
            return [
                'uid' => $response->result->uid,
                'url' => $response->result->rtmps->url,
                'key' => $response->result->rtmps->streamKey,
            ];
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    static function createMeeting($name)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get(env('AGORA_BACKEND_URL') . '/v1/login?project_id=' . env('AGORA_MEETING_PROJECT_ID') . '&platform_id=turnkey_web');
            $token = \json_decode($response->getBody()->getContents())->token;
            $response = $client->post(env('AGORA_BACKEND_URL') . '/v1/query', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                ],
                'body' => json_encode([
                    'operationName' => 'CreateChannel',
                    'variables' => [
                        'title' => $name,
                        'enablePSTN' => false
                    ],
                    'query' => 'mutation CreateChannel($title: String!, $enablePSTN: Boolean) {
                                createChannel(title: $title, enablePSTN: $enablePSTN) {
                                    passphrase {
                                        host
                                        view
                                        __typename
                                    }
                                    channel
                                    title
                                    pstn {
                                        number
                                        dtmf
                                        __typename
                                    }
                                    __typename
                                }
                            }',
                ])
            ]);
            $response = \json_decode($response->getBody()->getContents());
            return [
                'host' => $response->data->createChannel->passphrase->host,
                'view' => $response->data->createChannel->passphrase->view
            ];
        } catch (\Throwable $th) {
            return null;
        }
    }

    static function getSeatPriceByGUID($theatre_json, $guid)
    {
        $category = $theatre_json['seat_map'][$guid];
        $price = 0;
        foreach ($theatre_json['items'] as $item) {
            if ($item['categories'][0] === $category) {
                $price = $item['price_'];
            }
        }
        return $price;
    }
}
