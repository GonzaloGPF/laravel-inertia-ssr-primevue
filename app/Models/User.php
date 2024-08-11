<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Currencies;
use App\Enums\Languages;
use App\Enums\Roles;
use App\Notifications\ResetPassword;
use App\Traits\IsFilterable;
use App\Traits\NameFormatter;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

/**
 * @mixin IdeHelperUser
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory,
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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Roles::class,
            'language' => Languages::class,
            'currency' => Currencies::class,
        ];
    }

    public function sendPasswordResetNotification($token): void
    {
        Lang::setLocale($this->language->value);

        $this->notify(new ResetPassword($token));
    }

    protected function password(): Attribute
    {
        return Attribute::set(fn ($value) => Hash::make($value));
    }

    protected function email(): Attribute
    {
        return Attribute::set(fn ($value) => Str::of($value)->trim()->lower()->value());
    }
}
