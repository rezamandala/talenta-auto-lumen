<?php

namespace App\Talenta\v1\Request;

use Carbon\Carbon;
use DOMDocument;
use DOMElement;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\TransferStats;
use Symfony\Component\HttpFoundation\Request;

class AuthRequest extends AbstractRequest
{
    /** @var string|null $fileCookieJarName */
    protected ?string $fileCookieJarName = 'auth.cookies';

    /** @var string $redirectAfterLogin */
    private static string $redirectAfterLogin = 'employee/dashboard';

    /**
     * @param $email
     * @param $password
     * @return bool
     */
    public function login($email, $password): bool
    {
        $loginData = $this->loginData($email, $password);

        if (
            isset($loginData['request_uri']) &&
            str_contains($loginData['request_uri'], self::$redirectAfterLogin)
        ) {
            return true;
        }

        $url = null;
        $option = [
            'form_params' => $loginData['form_params'] ?? [],
            'on_stats' => function (TransferStats $stats) use (&$url) {
                /** @var Uri $url */
                $url = $stats->getEffectiveUri();
            }
        ];

        $this->executor(Request::METHOD_POST, $loginData['request_uri'], $option);

        return str_contains($url, self::$redirectAfterLogin);
    }

    /**
     * @param $email
     * @param $password
     * @return array
     */
    private function loginData($email, $password) : array
    {
        $data = [];

        try {
            $request = $this->request(Request::METHOD_GET, '', [
                'on_stats' => function (TransferStats $stats) use (&$url) {
                    /** @var Uri $url */
                    $url = $stats->getEffectiveUri();
                }
            ]);

            $body = $request->getBody() ? $request->getBody()->getContents() : null;

            $dom = new DOMDocument();

            @$dom->loadHtml($body);

        } catch (Exception|GuzzleException $exception) {
            return [
                'status' => [
                    'code' => '999',
                    'message' => $exception->getMessage()
                ]
            ];
        }

        $formNewUser = $dom->getElementById('new_user') ?? null;
        $inputHiddenTags = $formNewUser?->getElementsByTagName('input') ?? [];

        /** @var DOMElement $inputHiddenTag */
        foreach ($inputHiddenTags as $inputHiddenTag) {
            $inputType = $inputHiddenTag->getAttribute('type');
            $inputName = $inputHiddenTag->getAttribute('name');
            $inputValue = $inputHiddenTag->getAttribute('value');

            if ($inputType === 'hidden') {
                $data['form_params'][$inputName] = $inputValue;
            }
        }

        /** @var Uri $url */
        $data['request_uri'] = $url->jsonSerialize();
        $data['form_params']['user[email]'] = $email;
        $data['form_params']['user[password]'] = $password;

        return $data;
    }

    /**
     * @param Carbon|null $date
     * @return array
     */
    public function liveAttendanceHistory(Carbon $date = null) : array
    {
        $date = $date === null ? Carbon::now()->format(parent::$dateFormat) : $date->format(parent::$dateFormat);

        $uri = '/api/web/live-attendance/history';
        $option = [
            'query' => [
                'date' => $date
            ]
        ];

        return $this->executor(Request::METHOD_POST, $uri, $option);
    }
}
