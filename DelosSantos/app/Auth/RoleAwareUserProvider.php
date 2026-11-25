<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class RoleAwareUserProvider extends EloquentUserProvider
{
    public function __construct(Hasher $hasher, string $model)
    {
        parent::__construct($hasher, $model);
    }

    public function retrieveById($identifier): ?Authenticatable
    {
        $model = $this->createModel();

        return $this->eligibleQuery($model->newQuery())
            ->where($model->getAuthIdentifierName(), $identifier)
            ->first();
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        $model = $this->createModel();
        $rememberTokenName = $model->getRememberTokenName();

        if (empty($rememberTokenName)) {
            return null;
        }

        return $this->eligibleQuery($model->newQuery())
            ->where($model->getAuthIdentifierName(), $identifier)
            ->where($rememberTokenName, $token)
            ->first();
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (! isset($credentials['role'])) {
            return null;
        }

        $model = $this->createModel();
        $query = $this->eligibleQuery($model->newQuery());

        foreach ($credentials as $key => $value) {
            if (str_contains((string) $key, 'password') || in_array($key, ['token', 'remember_token'], true)) {
                continue;
            }

            if (is_array($value) || $value instanceof \Closure) {
                continue;
            }

            if ($key === 'email') {
                $query->whereRaw('LOWER(email) = ?', [Str::lower(trim((string) $value))]);
                continue;
            }

            $query->where($key, $value);
        }

        return $query->first();
    }

    private function eligibleQuery(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [User::STATUS_ACTIVE, User::STATUS_PENDING])
            ->where(function (Builder $roleQuery): void {
                $roleQuery
                    ->where('role', User::ROLE_ADMIN)
                    ->orWhereHas('member', function (Builder $memberQuery): void {
                        $memberQuery
                            ->whereIn('status', ['active', 'pending', 'inactive'])
                            ->whereNull('archived_at');
                    });
            });
    }
}
