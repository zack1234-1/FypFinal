<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\DeletionService;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $adminRole = Role::where('name', 'admin')->first();

        if ($adminRole) 
        {
            $customers = $adminRole->users()->get();
        }

        return view('superadmin.admins.index', ['customers' => $customers]);
    }
    

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|regex:/^[^\d]+$/',
            'last_name' => 'required|string|regex:/^[^\d]+$/',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|regex:/^\d+$/|unique:users,phone',
            'password' => 'required|string|min:1|confirmed',
            'password_confirmation' => 'required',
        ], [
            'first_name.required' => 'First name is required.',
            'first_name.string' => 'First name must be a string.',
            'first_name.regex' => 'First name cannot contain integers.',
            'last_name.required' => 'Last name is required.',
            'last_name.string' => 'Last name must be a string.',
            'last_name.regex' => 'Last name cannot contain integers.',
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email format.',
            'email.unique' => 'Email already exists.',
            'phone.required' => 'Phone number is required.',
            'phone.string' => 'Phone number must be a string.',
            'phone.unique' => 'Phone Number already exists.',
            'phone.regex' => 'Phone number can only contain digits.',
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',
            'password.min' => 'Password must be at least 1 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

       if ($validator->fails()) 
       {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }


        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name =  $request->last_name;
        $user->phone = $request->phone;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->status = '1';
        $user->role = 'admin';
        $user->save();
        $user->assignRole('admin');
        $admin = new Admin();
        $admin->user_id = $user->id;

        $admin->save();

        return redirect()->back()->with('success', 'Admin registered successfully.');
    }

    public function update(Request $request, $id)
    {
        $customer = User::findOrFail($id);

        $rules = [
            'first_name' => 'required|string|regex:/^[^\d]+$/|max:255',
            'last_name'  => 'required|string|regex:/^[^\d]+$/|max:255',
            'phone'      => 'required|string|regex:/^\d+$/|max:20|unique:users,phone,' . $customer->id,
            'email'      => 'required|email|max:255|unique:users,email,' . $customer->id,
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:1|confirmed';
            $rules['password_confirmation'] = 'required';
        }

        $messages = [
            'first_name.required' => 'First name is required.',
            'first_name.string'   => 'First name must be a string.',
            'first_name.regex'    => 'First name cannot contain integers.',
            'first_name.max'      => 'First name may not be greater than 255 characters.',
            'last_name.required'  => 'Last name is required.',
            'last_name.string'    => 'Last name must be a string.',
            'last_name.regex'     => 'Last name cannot contain integers.',
            'last_name.max'       => 'Last name may not be greater than 255 characters.',
            'phone.required'      => 'Phone number is required.',
            'phone.string'        => 'Phone number must be a string.',
            'phone.regex'         => 'Phone number can only contain digits.',
            'phone.max'           => 'Phone number may not be greater than 20 digits.',
            'phone.unique'        => 'Phone number already exists.',
            'email.required'      => 'Email is required.',
            'email.email'         => 'Invalid email format.',
            'email.unique'        => 'Email already exists.',
            'password.required'   => 'Password is required when updating.',
            'password.string'     => 'Password must be a string.',
            'password.min'        => 'Password must be at least 1 characters.',
            'password.confirmed'  => 'Password confirmation does not match.',
            'password_confirmation.required' => 'Please confirm the new password.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $customer->first_name = $request->first_name;
        $customer->last_name  = $request->last_name;
        $customer->phone      = $request->phone;
        $customer->email      = $request->email;

        if ($request->filled('password')) {
            $customer->password = Hash::make($request->password);
        }

        $customer->save();

        return redirect()->back()->with('success', 'Admin updated successfully.');
    }


    public function destroy(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        try {
            $user->delete();
            return redirect()->back()->with('success', 'Deleted admin successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete the user: ' . $e->getMessage());
        }
    }
}
