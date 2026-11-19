<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampusIdentityVerification extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending_review';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'campus_student_id',
        'npm',
        'status',
        'old_name',
        'old_npm',
        'old_email',
        'official_name',
        'official_email',
        'name_similarity',
        'match_reasons',
        'campus_payload',
        'submitted_at',
        'verified_at',
        'reviewed_at',
        'reviewed_by',
        'review_note',
    ];

    protected $casts = [
        'match_reasons' => 'array',
        'campus_payload' => 'array',
        'name_similarity' => 'float',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
