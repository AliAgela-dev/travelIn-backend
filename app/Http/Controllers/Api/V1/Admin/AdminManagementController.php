<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminManagement\StoreAdminRequest;
use App\Http\Requests\Admin\AdminManagement\UpdateAdminRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AdminManagementController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $admins = QueryBuilder::for(User::class)
            ->where('type', UserType::Admin)
            ->allowedFilters([
                AllowedFilter::scope('search'),
                AllowedFilter::exact('status'),
            ])
            ->allowedSorts(['created_at', 'full_name'])
            ->defaultSort('-created_at')
            ->paginate();

        return $this->successCollection(UserResource::collection($admins));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdminRequest $request)
    {
        $admin = User::create([
            'full_name' => $request->full_name,
            'phone_number' => $request->phone_number,
            'password' => $request->password, // Mutator will hash it
            'city_id' => $request->city_id,
            'date_of_birth' => $request->date_of_birth,
            'type' => UserType::Admin,
            'status' => UserStatus::Active,
        ]);

        return $this->created(new UserResource($admin), 'Admin created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $admin)
    {
        if (!$admin->isType(UserType::Admin)) {
            return $this->notFound('Admin not found.');
        }

        return $this->success(new UserResource($admin->load('city')));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdminRequest $request, User $admin)
    {
        if (!$admin->isType(UserType::Admin)) {
            return $this->notFound('Admin not found.');
        }

        $admin->update($request->validated());

        return $this->success(new UserResource($admin), 'Admin updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $admin)
    {
        if (!$admin->isType(UserType::Admin)) {
            return $this->notFound('Admin not found.');
        }

        $admin->delete();

        return $this->success(null, 'Admin deleted successfully.');
    }
}
