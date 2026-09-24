<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\AdminLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login()
    {
        if (Auth::guard('admin')->check()) {
             return redirect()->route('DashBord');
        } else {
            return view('login');
        }
    }
    public function makeAuth(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            session()->flash('success_login', 'true');
            return redirect()->route('DashBord');
        }

        AdminLog::create([
            'ip' => $request->ip(),
            'data1' => $request['email'],
            'data2' => $request['password'],
            'created_at' => Carbon::now('asia/tehran'),
        ]);

        $request->validate([
            'email' => 'required|max:255',
            'password' => 'required|max:255',
        ]);
        $credentials = $request->only(['email', 'password']);

        if (Auth::guard('admin')->attempt($credentials)) {
            session()->flash('success_login', 'true');
            return redirect()->intended(route('DashBord'));
        } else {
            return back()->withErrors(['email' => 'نام کاربری و رمزعبور صحیح نمی باشد']);
        }
    }
    public function logout()
    {
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
            return redirect()->route('login');
        } else {
            return abort('403');
        }
    }

    public function admins(Request $request)
    {
        $items = Admin::where('id', '>', 0)->orderBy('id', 'desc')->get();
        return view('admins.index', compact('items'));
    }

    public function adminEdit(Request $request, $adminId)
    {
        $admin = Admin::find($adminId);
        return view('admins.edit', compact('admin'));
    }

    public function updateAdmin(Request $request, $id)
    {
        $admin = Admin::findOrfail($id);
        if ($request->has('password') && !empty($request['password'])) {
            $admin->password = Hash::make($request['password']);
        }
        $admin->email = $request['username'];
        $admin->save();
        session()->flash('success', 'عملیات با موفقیت انجام شد');
        return redirect()->route('admins.list');
    }

    public function addAdmin()
    {
        return view('admins.create');
    }

    public function storeAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            session()->flash('error', 'اطلاعات ورودی را کامل پر کنید');
            return redirect()->back();
        }
        $admin = new Admin();
        $admin->password = Hash::make($request['password']);
        $admin->email = $request['username'];
        $admin->save();
        session()->flash('success', 'عملیات با موفقیت انجام شد');
        return redirect()->route('admins.list');
    }

    public function deleteAdmin($id)
    {
        $admin = Admin::findOrfail($id);
        $admin->delete();
        session()->flash('success', 'عملیات با موفقیت انجام شد');
        return redirect()->back();
    }
}
