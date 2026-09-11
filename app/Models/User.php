<?php

namespace App\Models;

use App\Authorization\Ability;
use App\Authorization\RoleAbility;
use App\Enums\UserRole;
use App\Models\Concerns\Auditable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $username
 * @property string|null $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property UserRole $role
 * @property bool $is_active
 * @property bool|null $show_dashboard_numbers
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['name', 'username', 'email', 'password', 'role', 'is_active', 'show_dashboard_numbers'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use Auditable, HasFactory, Notifiable, PasskeyAuthenticatable, SoftDeletes, TwoFactorAuthenticatable;

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Null means inherit the general settings default.
     *
     * @return Attribute<bool|null, bool|null>
     */
    protected function showDashboardNumbers(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value): ?bool {
                if ($value === null) {
                    return null;
                }

                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            },
            set: function (?bool $value): ?int {
                if ($value === null) {
                    return null;
                }

                return $value ? 1 : 0;
            },
        );
    }

    public function hasAbility(Ability|string $ability): bool
    {
        return RoleAbility::allows($this->role, $ability);
    }

    /**
     * @return list<string>
     */
    public function abilityValues(): array
    {
        return RoleAbility::values($this->role);
    }
}
