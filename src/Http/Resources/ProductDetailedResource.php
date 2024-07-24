<?php

namespace EscolaLms\Cart\Http\Resources;

use EscolaLms\Auth\Enums\AuthPermissionsEnum;
use EscolaLms\Cart\Http\Resources\ProductResource;
use EscolaLms\Cart\Models\User;
use Illuminate\Support\Facades\Auth;

class ProductDetailedResource extends ProductResource
{
    public function toArray($request): array
    {
        /** @var User $user */
        $user = $request ? $request->user() : Auth::user();
        $result = parent::toArray($request);
        if ($user->can(AuthPermissionsEnum::USER_LIST)) {
            $result['users']  = $this->getProduct()->users->map(fn (User $user) => ['id' => $user->getKey(), 'email' => $user->email, 'name' => $user->name])->toArray();
        }
        return $result;
    }
}
