<?php

if (isset($_GET['code'])) {
    XboxAuth::callback($_GET['code']);
} else {
    XboxAuth::redirect();
}



class XboxAuth
{
    protected static $settings = [
        'app_id' => 'YOUR APPLICATION ID',
        'secret' => 'YOUR APPLICATION SECRET',
    ];

    protected static function request($method, $url, array $headers = [], $content = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, [
            'Accept-Language: en-GB',
        ]));

        if (null !== $content) {
            // set body
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        }

        $response = curl_exec($ch);

        if (!$response) {
            die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
        }

        curl_close($ch);

        return $response;
    }

    public static function redirect()
    {
        $url = 'https://' . self::$settings['app_id'] . '.xauth.dev';

        header('Location: ' . $url);
        exit;
    }

    public static function callback($code)
    {
        $result = self::request(
            'POST',
            'https://claim.xauth.dev',
            [
                'Content-type: application/json',
            ],
            json_encode([
                'app_id' => self::$settings['app_id'],
                'secret' => self::$settings['secret'],
                'code' => $code,
            ])
        );

        $profile = json_decode($result);

        header('Content-Type: application/json');

        print json_encode($profile, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    }
}
