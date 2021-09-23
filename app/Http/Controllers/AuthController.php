<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

use \App\Helpers\UserHelper;

use App\Models\User;

class AuthController extends Controller
{
	protected function create(Request $request)
	{
		return User::create([
			'name' => $request['name'],
			'email' => $request['email'],
			'password' => Hash::make($request['password']),
			'api_token' => hash('sha256',Str::random(60)),
		]);
	}

	public function authenticate(Request $request)
	{
		$credentials = $request->only('email', 'password');

		if (Auth::attempt($credentials))
			return ['success' => true, 'user' => Auth::user()];
		else
			return ['success' => false];
	}
}
