<?php

namespace App\Http\Controllers\La;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function index()
    {
        return view('pages.users.index');
    }

    public function create()
    {
        return view('pages.users.create');
    }

    public function view($id)
    {
        return view('pages.users.view', ['userId' => $id]);
    }

    public function edit(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Check if request is AJAX/Iframe (common for Magnific Popup in this project)
        if ($request->ajax() || $request->has('ajax')) {
            return view('pages.users.modal-edit', compact('user'));
        }

        return view('pages.users.edit', ['userId' => $id]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'e_mail' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:6',
        ]);

        $data = [
            'name' => $validated['name'],
            'e_mail' => $validated['e_mail'],
            'phone' => $validated['phone'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        $user->update($data);

        if ($request->ajax() || $request->has('ajax')) {
            return response()->json(['success' => true, 'message' => 'Данные пользователя обновлены']);
        }

        return redirect()->route('users.index')->with('success', 'Пользователь обновлен');
    }
}
