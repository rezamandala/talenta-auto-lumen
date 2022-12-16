<?php

namespace App\Talenta\v1\Request;

use Carbon\Carbon;
use GuzzleHttp\Cookie\FileCookieJar;
use Symfony\Component\HttpFoundation\Request;

class LiveAttendanceRequest extends AbstractRequest
{
    /** @var string|null $fileCookieJarName */
    protected ?string $fileCookieJarName = 'live_attendance.cookies';

    /** @var string|null $accessToken */
    protected ?string $accessToken = null;

    /** @var bool $isClockedIn */
    protected bool $isClockedIn = true;

    /** @var bool $isClockedOut */
    protected bool $isClockedOut = true;

    protected bool $isOffDay = false;

    /** @var string $eventTypeClockIn */
    public static string $eventTypeClockIn = 'clock_in';

    /** @var string $eventTypeClockOut */
    public static string $eventTypeClockOut = 'clock_out';

    /** @var array $data */
    protected array $data = [
        'latitude' => null,
        'longitude' => null,
        'event_type' => null,
        'notes' => null,
        'selfie_photo' => null,
        'organisation_user_id' => null,
        'source' => null,
        'schedule_date' => null
    ];

    /** @var array $offDay */
    protected static array $offDay = [
        'saturday',
        'sunday'
    ];

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $defaultConfig['base_uri'] = 'https://api.mekari.com';

        $config = array_merge($defaultConfig, $config);

        parent::__construct($config);

        $this->data['latitude'] = env('TALENTA_LIVE_ATTENDANCE_LATITUDE');
        $this->data['longitude'] = env('TALENTA_LIVE_ATTENDANCE_LONGITUDE');
        $this->data['notes'] = '';
        $this->data['selfie_photo'] = null;
        $this->data['source'] = env('TALENTA_LIVE_ATTENDANCE_SOURCE');
        $this->data['schedule_date'] = $this->currentDate->format(parent::$dateFormat);
    }

    /**
     * @return array
     */
    public function clockIn(): array
    {
        $this->data['event_type'] = self::$eventTypeClockIn;

        $clockInTime = sprintf(
            '%s %s',
            $this->currentDate->format(parent::$dateFormat),
            env('TALENTA_CLOCK_IN_TIME', '09:00')
        );
        $clockInTimeStamp = Carbon::createFromFormat(
            sprintf('%s H:i', parent::$dateFormat), $clockInTime
        );

        if (
            ((int)$this?->currentDate?->timestamp >= (int)$clockInTimeStamp?->timestamp) &&
            !$this->isClockedIn() &&
            !$this->isOffDay()
        ) {
            return $this->executorCaller();
        }

        return ['status' => [
            'code' => '403',
            'message' => $this->isOffDay() ? __('CLOCK_IN_FAILED_DUE_OFF_DAY') : __('CLOCK_IN_TIME_DOES_NOT_MATCH')
        ]];
    }

    /**
     * @return array
     */
    public function clockOut(): array
    {
        $this->data['event_type'] = self::$eventTypeClockOut;

        $clockOutTime = sprintf(
            '%s %s',
            $this->currentDate->format(parent::$dateFormat),
            env('TALENTA_CLOCK_OUT_TIME', '18:00')
        );
        $clockOutTimeStamp = Carbon::createFromFormat(sprintf('%s H:i', parent::$dateFormat), $clockOutTime);

        if (
            ((int)$this?->currentDate?->timestamp >= (int)$clockOutTimeStamp?->timestamp) &&
            !$this->isClockedOut()  &&
            !$this->isOffDay()
        ) {
            return $this->executorCaller();
        }

        return ['status' => [
            'code' => '403',
            'message' => $this->isOffDay() ? __('CLOCK_OUT_FAILED_DUE_OFF_DAY') : __('CLOCK_OUT_TIME_DOES_NOT_MATCH')
        ]];
    }

    /**
     * @return array
     */
    protected function executorCaller(): array
    {
        $token = $this->parseToken();
        $userId = $this->parseUserId();

        $this->data['organisation_user_id'] = (string)$userId;

        $uri = '/internal/talenta-attendance-web/v1/organisations/2799/attendance_clocks';
        $option = [
            'json' => $this->data,
            'headers' => [
                'Authorization' => sprintf('Bearer %s', $token)
            ],
        ];

        return $this->executor(
            Request::METHOD_POST,
            $uri,
            $option
        );
    }

    /**
     * @return bool
     */
    public function isClockedIn(): bool
    {
        return $this->isClockedIn;
    }

    /**
     * @param bool $isClockedIn
     */
    public function setIsClockedIn(bool $isClockedIn): void
    {
        $this->isClockedIn = $isClockedIn;
    }

    /**
     * @return bool
     */
    public function isClockedOut(): bool
    {
        return $this->isClockedOut;
    }

    /**
     * @param bool $isClockedOut
     */
    public function setIsClockedOut(bool $isClockedOut): void
    {
        $this->isClockedOut = $isClockedOut;
    }

    /**
     * @return bool
     */
    public function isOffDay(): bool
    {
        $offDays = env('TALENTA_OFF_DAY', self::$offDay);
        $explode = explode(',', $offDays);

        array_walk($explode, function(&$value) {
            $value = strtoupper($value);
        });

        return in_array(strtoupper($this->currentDate->format('l')), $explode);
    }

    /**
     * @return string|null
     */
    private function parseToken(): ?string
    {
        $sessionToken = $this->parser('_session_token');

        return $sessionToken[1] ?? null;
    }

    /**
     * @return int|null
     */
    private function parseUserId(): ?int
    {
        $identity = $this->parser('_identity');
        $userId = json_decode($identity[1] ?? '', true);

        return $userId[0] ?? null;
    }

    /**
     * @param string $cookieName
     * @return array
     */
    private function parser(string $cookieName): array
    {
        $cookies = new FileCookieJar(
            storage_path(sprintf('cookies/%s', 'auth.cookies')),
            true
        );

        $cookieValue = $cookies->getCookieByName($cookieName)?->getValue() ?? null;
        $decode = urldecode($cookieValue);
        $split = substr($decode, strpos($decode, "a:2:") - 0);

        return unserialize($split) ?? [];
    }
}
