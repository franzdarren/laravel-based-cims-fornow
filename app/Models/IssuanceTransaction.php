<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The visit-specific detail extension of a transaction_log row whose type
 * is ISSUANCE. Reference number, date, user, and the issued lines all live
 * on the related TransactionLog — accessed here via convenience proxies so
 * views can keep reading $issuance->reference_no / ->date / ->lines.
 */
class IssuanceTransaction extends Model
{
    protected $table = 'issuance_transaction';

    protected $primaryKey = 'issuance_transaction_id';

    public $timestamps = false;

    protected $fillable = [
        'employee_no', 'employee_name', 'department', 'employee_supervisor',
        'chief_complaint', 'disposition', 'remarks', 'transaction_id',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(TransactionLog::class, 'transaction_id', 'transaction_id');
    }

    public function getIssuedByAttribute()
    {
        return $this->transaction?->user;
    }

    public function getReferenceNoAttribute()
    {
        return $this->transaction?->reference_no;
    }

    public function getDateAttribute()
    {
        return $this->transaction?->transaction_datetime;
    }

    public function getStatusAttribute()
    {
        return 'POSTED';
    }

    public function getLinesAttribute()
    {
        return $this->transaction?->lines ?? collect();
    }

    public static function nextReferenceNo(): string
    {
        $prefix = 'ISS-'.now()->format('Y-md').'-';
        do {
            $candidate = $prefix.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);
        } while (TransactionLog::where('reference_no', $candidate)->exists());

        return $candidate;
    }
}
