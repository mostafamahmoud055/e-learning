<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Traits\FileProcess;
use Illuminate\Http\Request;
use App\Traits\GeneralResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ProfileRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateProfileRequest;

class ProfileController extends Controller
{
    use GeneralResponse, FileProcess;

    public function __construct()
    {
        $this->middleware('jwtAuth')->except('share');
    }

    public function index()
    {
        return $this->returnData('Authenticated User', auth()->user());
    }

    public function share($username, $code)
    {
        $user = User::Where('username', $username)
            ->where('code', $code)
            ->first();
        if (!$user) {
            return $this->returnError('USER NOT FOUND', 400);
        }
        return $this->returnData('Authenticated User', auth()->user());
    }

    public function ViewMyCourses()
    {
        if (Auth::user()->role == 'teacher') {
            $CoursesOfUser = Auth::user()->courses;
        } else {
            
            $CoursesOfUser = Student::find(Auth::id())->courses()->with(['teacher' => function ($q) {
                $q->select('id', 'first_name','last_name');
            }])->get();
        }
        return $this->returnData('Authenticated User', $CoursesOfUser);
    }

    public function updateProfile(UpdateProfileRequest $request, User $user)
    {
        if (!$user) {
            return $this->returnError('USER NOT FOUND', 400);
        }
        $old_image = $user->image;
        $data = $request->except('photo');

        if ($path = $this->uploadFile($request, $user->role)) {
            $data['image'] = 'images/' . $path;
        }
        $user->update($data);

        if ($old_image && isset($data['image'])) {
            $old_image = str_replace('images/', '', $old_image);
            if ($old_image != "avatar.png") {
                Storage::delete($old_image);
            }
        }
        return $this->returnSuccessMessage('Profile updated successfully');
    }
}
