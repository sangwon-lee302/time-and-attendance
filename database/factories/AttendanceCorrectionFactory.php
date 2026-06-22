<?php

namespace Database\Factories;

use App\CorrectionStatus;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\BreakTime;
use App\Models\BreakTimeCorrection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use LengthException;

/**
 * @extends Factory<AttendanceCorrection>
 */
class AttendanceCorrectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user        = User::factory()->create();
        $clockedInAt = fake()->dateTimeBetween(today()->startOfDay());

        return [
            'attendance_id' => Attendance::factory()
                ->recycle($user)
                ->create()
                ->id,
            'requested_by'   => $user->id,
            'clocked_in_at'  => $clockedInAt,
            'clocked_out_at' => fake()->dateTimeBetween($clockedInAt),
            'remarks'        => fake()->realText(),
        ];
    }

    /**
     * Indicate that the model's status has to be 'approved'.
     */
    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => CorrectionStatus::Approved,
        ]);
    }

    /**
     * Indicate that the model is associated with the given attendance.
     */
    public function ofAttendance(Attendance $attendance): static
    {
        return $this->state(fn () => [
            'attendance_id' => $attendance->id,
            'requested_by'  => $attendance->user->id,
        ]);
    }

    /**
     * Indicate that the model has associated non-overlapping break time correction
     * resources.
     */
    public function hasNonOverlappingBreakTimeCorrections(
        int $count = 2,
        bool $shouldCreateNewBreakTime = false
    ): static {
        return $this->afterCreating(function (AttendanceCorrection $attendanceCorrection) use (
            $count,
            $shouldCreateNewBreakTime,
        ): void {
            if ($count <= 0) {
                return;
            }

            $shuffledBreakTimeIds = BreakTime::where(
                'attendance_id',
                $attendanceCorrection->attendance_id,
            )
                ->pluck('id')
                ->shuffle();

            $maxOfCreation = $shuffledBreakTimeIds->count() + 1;
            if ($count > $maxOfCreation) {
                throw new LengthException(
                    "Cannot create {$count} break time corrections; at most {$maxOfCreation}."
                );
            }

            $clockedInAt   = $attendanceCorrection->clocked_in_at;
            $clockedOutAt  = $attendanceCorrection->clocked_out_at;
            $segmentSize   = $clockedInAt->diffInSeconds($clockedOutAt) / $count;
            $hasBreakTimes = $shuffledBreakTimeIds->isNotEmpty();
            for ($i = 0; $i < $count; $i++) {
                $segmentStart = $clockedInAt->addSeconds($segmentSize * $i);
                $segmentEnd   = $segmentStart->addSeconds($segmentSize);

                $startedAt = fake()->dateTimeBetween($segmentStart, $segmentEnd);
                $endedAt   = fake()->dateTimeBetween($startedAt, $segmentEnd);

                BreakTimeCorrection::factory()
                    ->recycle($attendanceCorrection)
                    ->create([
                        'break_time_id' => ! $hasBreakTimes
                            || ($i === $count - 1 && $shouldCreateNewBreakTime)
                                ? null
                                : $shuffledBreakTimeIds[$i],
                        'started_at' => $startedAt,
                        'ended_at'   => $endedAt,
                        'created_at' => $attendanceCorrection->created_at,
                        'updated_at' => $attendanceCorrection->updated_at,
                    ]);
            }
        });
    }
}
