<?php

/**
 *  PHP Integration SMS-API
 *  Uzbekistan ООО “PLAY MOBILE”
 *  @version 2.0
 *  @author itachi
 */
class SMSPlayMobile
{
    private string $username;
    private string $password;
    private string $originator = "3700";
    private string $url = "https://send.smsxabar.uz/broker-api";
    private array $errors = [
        100 => [
            'en' => 'Internal server error',
            'ru' => 'Внутренняя ошибка сервера'
        ],
        101 => [
            'en' => 'Syntax error',
            'ru' => 'Синтаксическая ошибка'
        ],
        102 => [
            'en' => 'Account lock',
            'ru' => 'Аккаунт клиента заблокирован'
        ],
        103 => [
            'en' => 'Empty channel',
            'ru' => 'Не задан канал для отправки сообщений'
        ],
        104 => [
            'en' => 'Invalid priority',
            'ru' => 'Указано некорректное значение параметра priority'
        ],
        105 => [
            'en' => 'Too much IDs',
            'ru' => 'Передано слишком много идентификаторов сообщений'
        ],
        202 => [
            'en' => 'Empty recipient',
            'ru' => 'Адрес получателя не задан (кроме канала email)'
        ],
        204 => [
            'en' => 'Empty email address',
            'ru' => 'Адрес электронной почты получателя не задан (для канала email)'
        ],
        205 => [
            'en' => 'Empty message-id',
            'ru' => 'Идентификатор сообщения не задан'
        ],
        206 => [
            'en' => 'Invalid variables',
            'ru' => 'Указано некорректное значение параметра variables'
        ],
        301 => [
            'en' => 'Invalid localtime',
            'ru' => 'Указано некорректное значение параметра localtime'
        ],
        302 => [
            'en' => 'Invalid start-datetime',
            'ru' => 'Указано некорректное значение параметра start-datetime'
        ],
        303 => [
            'en' => 'Invalid end-datetime',
            'ru' => 'Указано некорректное значение параметра end-datetime'
        ],
        304 => [
            'en' => 'Invalid allowed-starttime',
            'ru' => 'Указано некорректное значение параметра allowed-starttime'
        ],
        305 => [
            'en' => 'Invalid allowed-endtime',
            'ru' => 'Указано некорректное значение параметра allowed-endtime'
        ],
        306 => [
            'en' => 'Invalid send-evenly',
            'ru' => 'Указано некорректное значение параметра send-evenly'
        ],
        401 => [
            'en' => 'Empty originator',
            'ru' => 'Адрес отправителя не указан'
        ],
        402 => [
            'en' => 'Empty application',
            'ru' => 'Приложение не указано'
        ],
        403 => [
            'en' => 'Empty ttl',
            'ru' => 'Значение ttl не указано (если задано несколько каналов отправки)'
        ],
        404 => [
            'en' => 'Empty content',
            'ru' => 'Содержимое сообщения не указано'
        ],
        405 => [
            'en' => 'Content error',
            'ru' => 'Неправильный формат содержимого контента'
        ],
        406 => [
            'en' => 'Invalid content',
            'ru' => 'Недопустимое значение контента для указанного канала'
        ],
        407 => [
            'en' => 'Invalid ttl',
            'ru' => 'Неправильно указано значение времени ожидания доставки'
        ],
        408 => [
            'en' => 'Invalid attached files',
            'ru' => 'Прикрепленные файлы имеют слишком большой объем'
        ],
        410 => [
            'en' => 'Invalid retry-attempts',
            'ru' => 'Неправильно указано значение количества попыток дозвона'
        ],
        411 => [
            'en' => 'Invalid retry-timeout',
            'ru' => 'Неправильно указано значение времени повторного дозвона'
        ],
    ];

    /**
     * @param string $username api login
     * @param string $password api password
     */
    function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Send a message to one recipient
     *
     * Message length:
     *
     * * GSM(latin, 7bit) - 160 characters
     *
     * * Unicode - 70 characters
     *
     *
     * @param string $recipient Phone number
     * @param string $contentText Message text
     *
     * @return void
     *
     * @throws LogicException Error from api
     */
    public function send(string $recipient, string $contentText): void
    {
        $object['messages'][] = [
            'recipient' => $recipient,
            'message-id' => '123456789',
            'sms' => [
                'originator' => $this->originator,
                'content' => ['text' => $contentText]
            ]
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->url .'/send',
            CURLOPT_USERPWD => ($this->username . ':' . $this->password),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($object),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);
        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $response = json_decode($response, true);
        curl_close($curl);

        if ($status != 200) {
            $message = __CLASS__ . ' returned HTTP status code ' . $status;
            if ($response && array_key_exists('error-code', $response))
                $message .= ': ' . $this->errors[$response['error-code']]['en'];
            throw new LogicException($message);
        }
    }

    /**
     * Send a message to a group of recipients
     *
     * Message length:
     *
     * * GSM(latin, 7bit) - 160 characters
     *
     * * Unicode - 70 characters
     *
     *
     * @param array $group
     *  * @param string $key Phone number
     *  * @param string $value Message text
     *
     * @return void
     *
     * @throws LogicException Error from api
     */
    public function sendGroup(array $group): void
    {
        foreach ($group as $recipient => $contentText) {
            $object['messages'][] = [
                'recipient' => $recipient,
                'message-id' => '123456789',
                'sms' => [
                    'originator' => $this->originator,
                    'content' => ['text' => $contentText]
                ]
            ];
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->url .'/send',
            CURLOPT_USERPWD => ($this->username . ':' . $this->password),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($object),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);
        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $response = json_decode($response, true);
        curl_close($curl);

        if ($status != 200) {
            $message = __CLASS__ . ' returned HTTP status code ' . $status;
            if ($response && array_key_exists('error-code', $response))
                $message .= ': ' . $this->errors[$response['error-code']]['en'];
            throw new LogicException($message);
        }
    }
}