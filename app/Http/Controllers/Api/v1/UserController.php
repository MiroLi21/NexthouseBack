<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Resources\User\UserLiteResource;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserResourceCollection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $data = UserResource::collection(User::all());

        return $this->ok($data);
    }
    public function getLite()
    {
        $data = UserLiteResource::collection(User::all());

        return $this->ok($data);
    }
    public function filter(Request $request)
    {
        $request->filled('limit') ? $limit = $request->limit : $limit = 10;

        $data = User::orderBy('id', 'desc');

        if (!$request->isNotFilled('email') && $request->email != '') {
            $data = $data->orWhere('email', 'like', '%' . $request->email . '%');
        }
        if (!$request->isNotFilled('name') && $request->name != '') {
            $data = $data->orWhere('name', 'like', '%' . $request->name . '%');
            $data = $data->orWhere('email', 'like', '%' . $request->name . '%');
        }

        $data = $data->paginate($limit);

        if (empty($data) || $data == null) {
            return $this->FailedResponse(__('general.loadFailed'));
        } else {
            return $this->ok(new UserResourceCollection($data));
        }
    }

    public function store(UserStoreRequest $request)
    {
        $user_id = 1;
        if (Auth::check()) {
            $user_id = Auth::id();
        }
        $user = new User();
        $user->active = false;
        $user->active = false;
        if (isset($request->user_type)) {
            $user->user_type = $request->user_type;
        }
        // $user->invite_code=$this->generateNumber(6);
        //
        $user->name = $request->name;
        $user->user_name = $request->user_name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->any_device = (isset($request->any_device) && $request->any_device != '') ? $request->any_device : 0;
        $user->active = (isset($request->active) && $request->active != '') ? $request->active : 0;
        if ($request->window_id == null) {
            $user->window_id = 1;
        } else {
            $user->window_id = $request->window_id;
        }
        $user->user_id = $user_id;

        try {
            $user->save();
            //    if(isset($request->role_id)) $user->assignRole($request->role_id);

            $access_token = $user->createToken($request->email)->plainTextToken;
            $user = User::find($user->id);
            foreach ($request->roles as $role_id) {
                (new RoleController())->set_role($role_id, $user->id);
            }

            return $this->ok([
                'user' => new UserResource($user),
                'token' => $access_token,
            ]);
        } catch (\Exception $e) {
            return $this->FailedResponse(__('general.saveUnsuccessfully'));
            // return $this->FailedResponse($e->getMessage(), __('general.saveUnsuccessfully'));
        }
    }

    public function show(string $id)
    {
        $data = User::find($id);

        return $this->ok(new UserResource($data));
    }

    public function update(Request $request, $id)
    {

        //$user = new Request(['user_id' => $user_id]);
        $Validate = $request->validate([
            // 'id' => ['integer', 'exists:users,id'],
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'user_name' => ['required', 'string', 'min:2', 'max:255'],
            //'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            // 'password' => ['nullable','required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::find($id);
        if (!isset($user) || $user == null || $user == '') {
            return $this->FailedResponse(__('general.saveUnsuccessfully'));
        }
        // if ($user->email != $request->email) {
        //     $validate = $request->validate([
        //         'name' => ['required', 'string', 'min:2', 'max:255'],
        //         'user_name' => ['required', 'string', 'min:2', 'max:255'],
        //         'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        //         'roles' => ['required', 'array', 'exists:roles,id'],
        //     ]);
        // }
        $user->name = $request->name;
        $user->user_name = $request->user_name;
        isset($request->email) && $request->email != '' ? $user->email = $request->email : '';
        isset($request->password) && $request->password != '' ? $user->password = Hash::make($request->password) : '';
        $user->any_device = (isset($request->any_device) && $request->any_device != '') ? $request->any_device : 0;
        $user->active = (isset($request->active) && $request->active != '') ? $request->active : 0;

        if ($request->window_id == null) {
            $user->window_id = 1;
        } else {
            $user->window_id = $request->window_id;
        }

        $user->save();
        $access_token = $user->createToken($request->email)->plainTextToken;
        $user->roles()->detach();
        foreach ($request->roles as $role_id) {
            $user->assignRole($role_id);
        }
        return $this->ok([
            'user' => new UserResource($user),
            'token' => $access_token,
        ], __('general.saveSuccessfully'));

        //return $this->FailedResponse(__('general.saveUnsuccessfully'));
    }
    public function updateMyPassword(Request $request)
    {
        $user = User::find(Auth::user()->id);
        if (!isset($user) || $user == null || $user == '') {
            return $this->FailedResponse(__('general.saveUnsuccessfully'));
        }
        isset($request->password) && $request->password != '' ? $user->password = Hash::make($request->password) : '';
        $user->save();
        $access_token = $user->createToken($user->email)->plainTextToken;
        return $this->ok([
            'user' => new UserResource($user),
            'token' => $access_token,
        ], __('general.saveSuccessfully'));
        //return $this->FailedResponse(__('general.saveUnsuccessfully'));
    }

    public function active($id)
    {
        $user = User::find($id);
        if (!isset($user) || $user == null || $user == '') {
            return $this->FailedResponse(__('general.saveUnsuccessfully'));
        }
        $user->active = true;
        $user->save();

        return $this->ok(new UserResource($user), __('general.saveSuccessfully'));
    }

    public function disActive($id)
    {
        $user = User::find($id);
        if (!isset($user) || $user == null || $user == '') {
            return $this->FailedResponse(__('general.saveUnsuccessfully'));
        }
        $user->active = false;
        $user->save();

        return $this->ok(new UserResource($user), __('general.saveSuccessfully'));
    }

    public function destroy(string $id)
    {
        $data = User::find($id);
        $data->delete();

        return $this->ok(null);
    }
}
