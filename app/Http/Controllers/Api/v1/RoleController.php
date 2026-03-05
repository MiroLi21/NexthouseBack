<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\RolePermissionResource;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Role::all();

        if (empty($data) || $data == null) {
            return $this->FailedResponse(__('general.loadFailed'));
        } else {
            return $this->ok(RolePermissionResource::collection($data));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'checkedPermission' => 'array|nullable',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);
        if ($request->filled('checkedPermission')) {
            $role->givePermissionTo($request->checkedPermission);
        }

        if (empty($role) || $role == null) {
            return $this->FailedResponse(__('general.saveUnsuccessfully'));
        } else {
            return $this->ok(new RolePermissionResource($role));
        }
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        $data = Role::findById($id, 'web');

        if (empty($data) || $data == null) {
            return $this->FailedResponse(__('general.loadFailed'));
        } else {
            return $this->ok(new RolePermissionResource($data));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        $request->validate([
            'name' => 'nullable|string',
            'permissions' => 'array|nullable',
            'checkedPermission' => 'array|nullable',
        ]);

        $data = Role::findById($id, 'web');
        if ($request->filled('name')) {
            $data->name = $request->name;
        }
        $data->update();
        // if($request->filled('permissions'))
        if ($request->filled('checkedPermission')) {
            $data->syncPermissions();
        }
        //$data->syncPermissions($request->permissions);
        $data->syncPermissions($request->checkedPermission);

        if (empty($data) || $data == null) {
            return $this->FailedResponse(__('general.loadFailed'));
        } else {
            return $this->ok(new RolePermissionResource($data));
        }
    }

    /**
     * set role for user.
     */
    public function set_role(int $role_id, int $user_id)
    {
        $user = User::find($user_id);
        $user->assignRole($role_id);

        if (empty($user) || $user == null) {
            return $this->FailedResponse(__('general.loadFailed'));
        } else {
            return $this->ok(new UserResource($user));
        }
    }

    /**
     * remove role form user.
     */
    public function remove_role(int $role_id, int $user_id)
    {
        $user = User::find($user_id);
        $user->removeRole($role_id);

        if (empty($user) || $user == null) {
            return $this->FailedResponse(__('general.loadFailed'));
        } else {
            return $this->ok(new UserResource($user));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        $data = Role::findById($id, 'web');
        $data->delete();

        if (empty($data) || $data == null) {
            return $this->FailedResponse(__('general.deleteUnsuccessfully'));
        } else {
            return $this->ok(new RolePermissionResource($data));
        }
    }
}
