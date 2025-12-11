<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = request('id');
        $theRole = request()->role;

        $validations = [
            'name' => 'required',
            'username' => ['required', function ($name, $value, $fail) use ($id) {
                if (\App\Models\User::where('username', strtolower($value))->where('id', '!=', $id)->withTrashed()->exists()) {
                    $fail("Username already exists.");
                }
            }],
            'phone_number' => ['required', function ($name, $value, $fail) use ($id) {
                if (\App\Models\User::where('phone_number', strtolower($value))->where('id', '!=', $id)->withTrashed()->exists()) {
                    $fail("Phone number already exists.");
                }
            }],
            'employee_id' => [function ($name, $value, $fail) use ($id) {
                if (\App\Models\User::where('employee_id', strtolower($value))->where('employee_id', '!=', '')->where('id', '!=', $id)->whereNotNull('employee_id')->withTrashed()->exists()) {
                    $fail("Employee ID already exists.");
                }
            }],
            'role' => 'required'
        ];

        return $validations;
    }
}