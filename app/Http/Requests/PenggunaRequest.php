<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PenggunaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Ambil ID user saat edit, null saat create
        $userId = $this->route('id');

        return [
            'fullname' => 'required|string|max:255',
            'nickname' => 'required|string|max:100',
            'gender' => 'required|in:Male,Female',

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($userId), // ignore record yang sedang diedit
                // ->where(fn ($query) => $query->where('active', 1)),
            ],

            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'phone' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'phone')->ignore($userId),
            ],
            'status' => 'required|in:Active,Not Active',
            'roles' => 'required', // pastikan minimal 1 role dipilih

            // Password wajib saat create, nullable saat edit
            'password' => $this->isMethod('post')
                ? 'required|string|same:confirm_password|min:6'
                : 'nullable|string|same:confirm_password|min:6',

            'confirm_password' => $this->isMethod('post')
                ? 'required|string|min:6'
                : 'nullable|string|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'fullname.required' => 'Full name is required',
            'nickname.required' => 'Nickname is required',
            'gender.required' => 'Gender is required',
            'email.required' => 'Email is required',
            'email.email' => 'Invalid email format',
            'email.unique' => 'Email has already been taken',
            'username.required' => 'Username is required',
            'username.unique' => 'Username has already been taken',
            'phone.required' => 'Phone number is required',
            'status.required' => 'Status is required',
            'roles.required' => 'Role is required',
            'password.required' => 'Password is required',
            'password.same' => 'Password and confirmation do not match',
            'confirm_password.required' => 'Password confirmation is required',
        ];
    }
}
