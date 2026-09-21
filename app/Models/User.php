<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'position', 'line_manager_id', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'photo_updated_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isReviewer(): bool
    {
        return $this->role === UserRole::Reviewer;
    }

    public function isEmployee(): bool
    {
        return $this->role === UserRole::Employee;
    }

    /**
     * Staff this user may create appraisals for: everyone for an admin; for a
     * reviewer, only their direct reports (line manager) and anyone they are
     * already the assigned reviewer for. Never themselves.
     */
    public function appraisableStaff(): Builder
    {
        $query = User::query()->where('is_active', true);

        if ($this->isAdmin()) {
            return $query;
        }

        if (! $this->isReviewer()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('id', '!=', $this->id)->where(function (Builder $q) {
            $q->where('line_manager_id', $this->id)
                ->orWhereIn('id', Appraisal::query()->where('reviewer_id', $this->id)->select('user_id'));
        });
    }

    public function photo(): HasOne
    {
        return $this->hasOne(UserPhoto::class);
    }

    public function hasPhoto(): bool
    {
        return $this->photo_updated_at !== null;
    }

    /** URL of the staff photo, versioned so a replaced photo isn't served from cache. */
    public function photoUrl(): ?string
    {
        return $this->hasPhoto()
            ? route('users.photo', ['user' => $this, 'v' => $this->photo_updated_at->timestamp])
            : null;
    }

    public function lineManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'line_manager_id');
    }

    public function directReports(): HasMany
    {
        return $this->hasMany(User::class, 'line_manager_id');
    }

    public function appraisals(): HasMany
    {
        return $this->hasMany(Appraisal::class, 'user_id');
    }

    public function reviewedAppraisals(): HasMany
    {
        return $this->hasMany(Appraisal::class, 'reviewer_id');
    }

    public function professionalDevelopment(): HasMany
    {
        return $this->hasMany(ProfessionalDevelopment::class);
    }

    public function auditLogEntries(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function uploadedAttachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'uploaded_by');
    }
}
