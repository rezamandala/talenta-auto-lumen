<?php

namespace App\Console\Commands;

use App\Talenta\v1\Request\AuthRequest;
use App\Talenta\v1\Request\LiveAttendanceRequest;
use Illuminate\Console\Command;

class TalentaAuto extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'talenta:auto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto clock in & clock out Talenta.';

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
     * @return void
     */
    public function handle(): void
    {
        $auth = new AuthRequest();

        $email = env('TALENTA_USER_EMAIL');
        $password = env('TALENTA_USER_PASSWORD');

        $login = $auth->login($email, $password);

        if (!$login) {
            $this->warn(__('LOGIN_FAILED'));

            return;
        }

        $this->info(__('LOGIN_SUCCESS'));

        $history = $auth->liveAttendanceHistory();

        $dataHistories = $history['data']['history'] ?? [];

        $isClockedIn = false;
        $clockedInData = [];

        $isClockedOut = false;
        $clockedOutData = [];

        foreach ($dataHistories as $dataHistory) {
            $eventType = $dataHistory['event_type'] ?? null;

            if ($eventType === LiveAttendanceRequest::$eventTypeClockIn) {
                $isClockedIn = true;
                $clockedInData = $dataHistory;
            }

            if ($eventType === LiveAttendanceRequest::$eventTypeClockOut) {
                $isClockedOut = true;
                $clockedOutData = $dataHistory;
            }
        }

        $liveAttendance = new LiveAttendanceRequest();
        $liveAttendance->setCompanyId($auth->getCompanyId());
        $liveAttendance->setIsClockedIn($isClockedIn);
        $liveAttendance->setIsClockedOut($isClockedOut);

        if (!$isClockedIn) {
            $this->info(__('CLOCK_IN'));
            $this->info(json_encode($liveAttendance->clockIn()));
        }
        if ($isClockedIn) {
            $this->info(__('CLOCKED_IN'));
            $this->info(json_encode($clockedInData));
        }

        if (!$isClockedOut) {
            $this->info(__('CLOCK_OUT'));
            $this->info(json_encode($liveAttendance->clockOut()));
        }
        if ($isClockedOut) {
            $this->info(__('CLOCKED_OUT'));
            $this->info(json_encode($clockedOutData));
        }
    }
}
