<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = QueryBuilder::for(User::class)
            ->with('city')
            ->allowedFilters([
                AllowedFilter::scope('search'),
                AllowedFilter::exact('type'),
                AllowedFilter::exact('status'),
            ])
            ->allowedSorts(['created_at', 'full_name'])
            ->defaultSort('-created_at')
            ->paginate();

        return $this->successCollection(UserResource::collection($users));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return $this->success(new UserResource($user->load('city')));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());

        return $this->success(new UserResource($user), 'User updated successfully.');
    }
}
