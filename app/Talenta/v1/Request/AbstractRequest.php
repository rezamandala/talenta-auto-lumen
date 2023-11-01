<?php

namespace App\Talenta\v1\Request;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractRequest extends Client
{
    /** @var string|null $fileCookieJarName */
    protected ?string $fileCookieJarName = 'cookies.cookies';

    /** @var string $dateFormat */
    protected static string $dateFormat = 'Y-m-d';

    public static string $checkDateFormat = 'Y-m-d H:i:s';

    /** @var FileCookieJar|null $fileCookieJar */
    protected ?FileCookieJar $fileCookieJar = null;

    /** @var string|null $accessToken */
    protected ?string $accessToken = null;

    protected ?string $companyId = null;

    /** @var Carbon|null $currentDate */
    public ?Carbon $currentDate = null;

    /** @var Carbon|null $clockInTimeStamp */
    public ?Carbon $clockInTimeStamp = null;

    /** @var Carbon|null $clockOutTimeStamp */
    public ?Carbon $clockOutTimeStamp = null;

    /** @var array $responseData */
    protected array $responseData = ['status' => ['code' => '204', 'message' => 'NO_DATA'], 'data' => null];

    /** @var int $retryCounter */
    private int $retryCounter = 0;

    /** @var int $maxRetry */
    private static int $maxRetry = 3;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $defaultConfig = [
            'base_uri' => env('TALENTA_BASE_URL'),
            'cookies' => $this->getFileCookieJar($this->fileCookieJarName),
        ];

        $config = array_merge($defaultConfig, $config);

        $this->currentDate = Carbon::now();

        $clockInTime = sprintf(
            '%s %s',
            $this->currentDate->format(self::$dateFormat),
            env('TALENTA_CLOCK_IN_TIME', '09:00')
        );
        $this->clockInTimeStamp = Carbon::createFromFormat(
            sprintf('%s H:i', self::$dateFormat), $clockInTime
        );

        $clockOutTime = sprintf(
            '%s %s',
            $this->currentDate->format(self::$dateFormat),
            env('TALENTA_CLOCK_OUT_TIME', '18:00')
        );
        $this->clockOutTimeStamp = Carbon::createFromFormat(sprintf('%s H:i', self::$dateFormat), $clockOutTime);

        parent::__construct($config);
    }

    /**
     * @param null $fileName
     * @return FileCookieJar
     */
    public function getFileCookieJar($fileName = null): FileCookieJar
    {
        $this->fileCookieJar = new FileCookieJar(
            storage_path(sprintf('cookies/%s', $fileName ?: $this->fileCookieJarName)),
            true
        );

        return $this->fileCookieJar;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return array
     */
    protected function executor(string $method, string $uri = '', array $options = []): array
    {
        try {
            $execute = $this->request($method, $uri, $options);

            $bodyContent = $execute->getBody()?->getContents() ?? '';
            $decode = json_decode($bodyContent, true);

            $data = is_array($decode) ? $decode : $this->responseData;
        } catch (Exception|GuzzleException|ServerException|ClientException $exception) {
            $this->responseData['status']['code'] = '999';
            $this->responseData['status']['message'] = $exception->getMessage();

            $data = $this->responseData;

            if ($exception instanceof ServerException || $exception instanceof ClientException) {
                $statusCode = $exception->getResponse()->getStatusCode();
                $bodyContent = $exception->getResponse()?->getBody()?->getContents() ?? [];
                $decode = json_decode($bodyContent, true);

                $data = is_array($decode) ? $decode : $this->responseData;

                if ($statusCode === Response::HTTP_UNAUTHORIZED) {
                    $this->responseData['status']['code'] = (string)Response::HTTP_UNAUTHORIZED;
                    $this->responseData['status']['message'] = $decode['error_description'] ?? __('UNAUTHORIZED');
                }
            }
        }

        return $data;
    }

    /**
     * @return string|null
     */
    public function getCompanyId(): ?string
    {
        return $this->companyId;
    }

    /**
     * @param string|null $companyId
     */
    public function setCompanyId(?string $companyId): void
    {
        $this->companyId = $companyId;
    }
}
