<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Currencies;
use App\Enums\Languages;
use App\Enums\Roles;
use App\Interfaces\HasFilesInterface;
use App\Notifications\ResetPassword;
use App\Traits\HasFiles;
use App\Traits\IsFilterable;
use App\Traits\NameFormatter;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @mixin IdeHelperUser
 */
class User extends Authenticatable implements HasFilesInterface, HasLocalePreference, MustVerifyEmail
{
    use HasFactory,
        HasFiles,
        IsFilterable,
        NameFormatter,
        Notifiable,
        SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'password',
        'currency',
        'language',
        'age',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'avatar',
    ];

    protected $appends = [
        'avatar_url',
    ];

    /**
     * The avatar is shown wherever the user is: in the lists and in the menu of the logged one.
     */
    protected $with = [
        'avatar',
    ];

    /**
     * Mirrors the column defaults declared in the users table migration, so a
     * freshly-instantiated model (before any database round-trip) reflects the
     * same values it would have once persisted.
     */
    protected $attributes = [
        'role' => Roles::USER->value,
        'language' => Languages::ES->value,
        'currency' => Currencies::EURO->value,
    ];

    public function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Roles::class,
            'language' => Languages::class,
            'currency' => Currencies::class,
            'age' => 'integer',
        ];
    }

    public function fileCollections(): array
    {
        return [
            'avatar' => ['single' => true, 'mimes' => ['jpg', 'jpeg', 'png', 'webp'], 'max' => 2048],
        ];
    }

    public function avatar(): MorphOne
    {
        return $this->fileOf('avatar');
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPassword($token));
    }

    public function preferredLocale(): string
    {
        return $this->language?->value ?? Languages::ES->value;
    }

    protected function password(): Attribute
    {
        return Attribute::set(fn ($value) => Hash::make($value));
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::get(fn () => $this->avatar?->url);
    }

    protected function email(): Attribute
    {
        return Attribute::set(fn ($value) => Str::of($value)->trim()->lower()->value());
    }
}
