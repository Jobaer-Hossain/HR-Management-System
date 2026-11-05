<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendancePolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'policy_name',
        'check_in_start',
        'check_in_end',
        'check_out_start',
        'check_out_end',
        'grace_period_minutes',
        'enforce_checkin_cutoff',
        'enforce_checkout_cutoff',
        'auto_block_message',
    ];

    protected $casts = [
        'enforce_checkin_cutoff' => 'boolean',
        'enforce_checkout_cutoff' => 'boolean',
        'grace_period_minutes' => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get or create default policy for a tenant
     */
    public static function forTenant(int $tenantId): self
    {
        return static::firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'policy_name' => 'Standard Shift Policy',
                'check_in_start' => '07:00:00',
                'check_in_end' => '10:30:00',
                'check_out_start' => '16:00:00',
                'check_out_end' => '23:59:00',
                'grace_period_minutes' => 15,
                'enforce_checkin_cutoff' => true,
                'enforce_checkout_cutoff' => false,
                'auto_block_message' => 'Check-in window closed. Please submit a Manual Attendance Request for Branch Manager approval.',
            ]
        );
    }

    /**
     * Check if a given time is allowed for check-in
     * Returns [bool $allowed, string $reason]
     */
    public function canCheckIn(?string $time = null): array
    {
        $time = $time ?? date('H:i:s');
        $timeTs = strtotime($time);
        $startTs = strtotime($this->check_in_start);
        $endTs = strtotime($this->check_in_end);

        if ($this->enforce_checkin_cutoff && $endTs && $timeTs > $endTs) {
            $formattedCutoff = date('h:i A', $endTs);
            return [
                'allowed' => false,
                'code' => 'AFTER_CUTOFF',
                'message' => "Check-in window closed at {$formattedCutoff}. Direct check-in is blocked after cutoff. Please submit a Manual Attendance Request for Branch Manager approval."
            ];
        }

        if ($startTs && $timeTs < $startTs) {
            $formattedStart = date('h:i A', $startTs);
            return [
                'allowed' => false,
                'code' => 'BEFORE_START',
                'message' => "Check-in opens at {$formattedStart}. It is too early to check in."
            ];
        }

        return ['allowed' => true, 'code' => 'ALLOWED', 'message' => 'Check-in allowed.'];
    }

    /**
     * Check if a given time is allowed for check-out
     */
    public function canCheckOut(?string $time = null): array
    {
        $time = $time ?? date('H:i:s');
        $timeTs = strtotime($time);
        $startTs = strtotime($this->check_out_start);
        $endTs = strtotime($this->check_out_end);

        if ($this->enforce_checkout_cutoff && $endTs && $timeTs > $endTs) {
            $formattedCutoff = date('h:i A', $endTs);
            return [
                'allowed' => false,
                'code' => 'AFTER_CUTOFF',
                'message' => "Checkout window closed at {$formattedCutoff}."
            ];
        }

        return ['allowed' => true, 'code' => 'ALLOWED', 'message' => 'Checkout allowed.'];
    }
}
