<?php

namespace App\Http\Controllers\User;

use App\Mail\UserCreated;
use App\Mail\UserMailChange;
use App\Transformers\UserTransformer;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Mail;

class UserController extends ApiController
{
    public function __construct()
    {
        $this->middleware('client.credentials')->only(['store','resend']);

        $this->middleware('auth:api')->except(['store','resend', 'verify']);

        $this->middleware('transform.input:'. UserTransformer::class)->only(['store', 'update']);

        $this->middleware('scope:manage-account')->only(['show', 'update']);
        $this->middleware('can:view,user')->only('show');
        $this->middleware('can:update,user')->only('update');
        $this->middleware('can:delete,user')->only('destroy');
    }


    public function index()
    {
        $this->allowedAdminAction(); // gate

        $users = User::all();

        return $this->showAll($users);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required | email | unique:users',
            'password' => 'required | min:6 | confirmed',
        ];

        $this->validate($request, $rules);

        $data = $request->all();
        $data['password'] = bcrypt($request->password);
        $data['verified'] = User::UNVERIFIED_USER;
        $data['verification_token'] = User::generateVerificationCode();
        $data['admin'] = User::REGULAR_USER;

        $user = User::create($data);

        if ($user)
        {
            retry(5, function () use($user) {
                Mail::to($user)->send(new UserCreated($user));
            }, 100);
        }

        return $this->showOne($user, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return $this->showOne($user);
    }


    public function update(Request $request, User $user)
    {
        $rules = [
            'email' => 'email | unique:users, email,'. $user->id,
            'password' => 'min:6 | confirmed',
            'admin' => 'in:'. User::ADMIN_USER . ',' . User::REGULAR_USER,
        ];

        if ($request->has('name'))
        {
            $user->name = $request->input('name');
        }

        if ($request->has('email') && $user->email != $request->input('email'))
        {
            $user->verified = User::UNVERIFIED_USER;
            $user->verification_token = User::generateVerificationCode();
            $user->email = $request->input('email');
            $user->email_verified_at = null;

            if ($user->isDirty('email'))
            {
                retry(5, function () use($user) {
                    Mail::to($user)->send(new UserMailChange($user));
                }, 100);
            }
        }

        if ($request->has('password'))
        {
            $user->password = bcrypt($request->input('password'));
        }

        if ($request->has('admin'))
        {
            $this->allowedAdminAction(); // gate

            if (!$user->isVerified())
            {
                return $this->errorResponse('Only verified users can modify the admin filed!', 409);
            }
            $user->admin = $request->input('admin');
        }

        if (!$user->isDirty())
        {
            return $this->errorResponse('You need to specify a different value to update', 422);
        }

        $user->save();

        return $this->showOne($user);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(User $user)
    {
        $user->delete();

        return $this->showOne($user);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return $this->showOne($user);
    }

    public function verify($token)
    {
        $user = User::where('verification_token', $token)->firstOrFail();

        $user->verified = User::VERIFIED_USER;
        $user->verification_token = null;
        $user->email_verified_at = Carbon::now();

        $user->save();

        return $this->showMessage('This account has been verified successfully!');
    }

    public function resend(User $user)
    {

        if ($user->isVerified())
        {
            return $this->errorResponse('This user is already verified', 409);
        }

        retry(5, function () use($user) {
            Mail::to($user)->send(new UserCreated($user));
        }, 100);

        return $this->showMessage('The verification email has been re-sent');
    }
}
