<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MedicineIntake;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SendIntakeReminders extends Command
{
protected $signature = 'send:intakes-reminders {--minutes=15}';
    protected $description = 'Send SMS reminders to patients shortly before their scheduled intake time.';

    protected $sms;

    public function __construct(SmsService $sms)
    {
        parent::__construct();
        $this->sms = $sms;
    }

    public function handle()
    {
        $minutes = (int)$this->option('minutes');
        if ($minutes <= 0) $minutes = 15;

        $now = Carbon::now();
        $windowEnd = $now->copy()->addMinutes($minutes);

        Log::info('SendIntakeReminders: scanning intakes', 
        ['now' => $now->toDateTimeString(), 
        'window_end' => $windowEnd->toDateTimeString()]);

        $intakes = MedicineIntake::with('user','medicine')
            ->where('status', false)
            ->where('sms_notified', false)
            ->whereBetween('intake_time', [$now, $windowEnd])
            ->get();

        foreach ($intakes as $intake) {
            try {
                $user = $intake->user;
                if (!$user || empty($user->phone_number)) {
                    Log::warning('SendIntakeReminders: user missing phone for intake', ['intake_id' => $intake->id]);
                    continue;
                }

                $medicineName = $intake->medicine ? $intake->medicine->medicine_name : 'medicine';
                $timeStr = Carbon::parse($intake->intake_time)->format('Y-m-d H:i');

                $msg = "Reminder: It's almost time for your medicine intake ({$medicineName}) scheduled at {$timeStr}. Please confirm when taken.";

                $res = $this->sms->send($user->phone_number, $msg);

                Log::info('SendIntakeReminders: sms send result', ['intake_id' => $intake->id, 'result' => $res]);

                DB::beginTransaction();
                $intake->sms_notified = true;
                $intake->sms_notified_at = Carbon::now();
                $intake->save();
                DB::commit();

            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('SendIntakeReminders: failed to send sms for intake', 
                ['intake_id' => $intake->id, 
                'error' => $e->getMessage()]);
            }
        }

        $this->info('SendIntakeReminders completed. Processed: ' . $intakes->count());
        return 0;
    }
}
