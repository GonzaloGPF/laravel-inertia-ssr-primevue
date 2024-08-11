<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{use AllowDynamicProperties;use App\Enums\Currencies;use App\Enums\Languages;use App\Enums\Roles;use Database\Factories\UserFactory;use Eloquent;use Illuminate\Database\Eloquent\Builder;use Illuminate\Notifications\DatabaseNotification;use Illuminate\Notifications\DatabaseNotificationCollection;use Illuminate\Support\Carbon;
/**
 *
 *
 * @property int $id
 * @property string|null $name
 * @property string $email
 * @property string|null $phone
 * @property Carbon|null $email_verified_at
 * @property mixed $password
 * @property Roles $role
 * @property Languages $language
 * @property Currencies $currency
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static UserFactory factory($count = null, $state = [])
 * @method static Builder|User filter(array $input = [], $filter = null)
 * @method static Builder|User getOrPaginate()
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User onlyTrashed()
 * @method static Builder|User paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|User query()
 * @method static Builder|User simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|User whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereCurrency($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereEndsWith($column, $value, $boolean = 'and')
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereLanguage($value)
 * @method static Builder|User whereLike($column, $value, $boolean = 'and')
 * @method static Builder|User whereName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User wherePhone($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereRole($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User withTrashed()
 * @method static Builder|User withoutTrashed()
 * @mixin Eloquent
 */
	#[AllowDynamicProperties]
	class IdeHelperUser {}
}

