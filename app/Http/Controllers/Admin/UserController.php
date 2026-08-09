<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        return view('hr.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route('hr.users.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // UI uses modal, fallback to index
        return redirect()->route('hr.users.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        // UI uses modal, fallback to index
        return redirect()->route('hr.users.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:users,code',
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|unique:users,username',
            'password' => 'nullable|string|min:6',
            'department' => 'nullable|string',
            'role' => 'required|string|in:admin,staff,viewer',
            'permissions' => 'nullable|array',
            'allowed_houses' => 'nullable|array'
        ]);

        $data = $request->except('password');
        
        $password = $request->filled('password') ? $request->password : '123456';
        $data['password'] = Hash::make($password);

        if (!$request->has('permissions')) {
            $data['permissions'] = [];
        }

        if (!$request->has('allowed_houses')) {
            $data['allowed_houses'] = [];
        }

        User::create($data);

        return redirect()->route('hr.users.index')->with('success', 'Thêm nhân viên thành công!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'code' => ['required', 'string', Rule::unique('users')->ignore($user->id)],
            'name' => 'required|string|max:255',
            'username' => ['nullable', 'string', Rule::unique('users')->ignore($user->id)],
            'department' => 'nullable|string',
            'role' => 'required|string|in:admin,staff,viewer',
            'password' => 'nullable|string|min:6',
            'permissions' => 'nullable|array',
            'allowed_houses' => 'nullable|array'
        ]);

        $data = $request->except('password');
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Đảm bảo không bị null (khi không checkbox nào được chọn, request sẽ không có 'permissions')
        if (!$request->has('permissions')) {
            $data['permissions'] = [];
        }

        if (!$request->has('allowed_houses')) {
            $data['allowed_houses'] = [];
        }

        $user->update($data);

        return redirect()->route('hr.users.index')->with('success', 'Cập nhật nhân viên thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('hr.users.index')->with('error', 'Không thể xóa chính tài khoản đang đăng nhập!');
        }

        $user->delete();
        return redirect()->route('hr.users.index')->with('success', 'Đã xóa nhân viên!');
    }
}
