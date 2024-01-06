<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Traits\GeneralResponse;
use App\Models\StudentAssessment;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Console\Input\Input;

class StudentController extends Controller
{
    use GeneralResponse;
    private array $list_of_students = [];
    private array $search_array = [];
    private array $student_filter = [];
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('jwtAuth');
    }

    public function filter(Request $request)
    {
        $this->authorize('student-search');
        $request->validate([
            'course_ignore' => 'sometimes|required|exists:courses,id|integer',
            "grade"    => ['sometimes', 'required', 'array', 'min:1'],
            'grade.*' => ['required', 'in:1,2,3,4,5,6,7,8,9,10,11,12'],
            "class"    => ['sometimes', 'required', 'array', 'min:1'],
            "class.*"  => ['required', 'in:A,B,C,D,E'],
            "course_id"    => ['sometimes', 'required', 'array', 'min:1'],
            "course_id.*"  => ['required', 'string', 'max:20'],
            'name' => ['sometimes', 'required', 'string', 'max:20'],
        ]);
        $course_ignore = $request->course_ignore ?? null;
        $course_id = $request->course_id ?? null;

        if ($request->grade) {
            $this->filterFunc($request, $course_ignore, $course_id);
        }
        if ($request->class) {
            unset($request['grade']);
            $this->filterFunc($request, $course_ignore, $course_id);
        }
        if ($request->name) {
            $email = strpos($request->name, '@');
            if ($email) {
                $request->merge(["email" => explode('@', $request->name)[0]]);
                unset($request['name']);
                $this->search($request);
            } else {
                $this->search($request);
            }
        }

        if (!$request->name && !$request->email && !$request->grade && !$request->class) {
            return $this->index($course_id);
        }

        if (!count($this->list_of_students)) {
            return $this->returnError("Student not found", 404);
        }
        return $this->returnData("Students Found", $this->list_of_students);
    }

    public function search($request)
    {
        if (!count($this->list_of_students)) {
            $container = Student::query()
                ->when($request->name ?? false, function ($query, $value) {
                    $query->where('users.first_name', 'LIKE', "{$value}%");
                })->when($request->email ?? false, function ($query, $value) {
                    $query->where('users.email', 'LIKE', "{$value}%");
                })->get();
            foreach ($container as $student) {
                $this->list_of_students[] = $student;
            }
        } else {
            $container = $this->list_of_students;
            $item = isset($request->name) ? $request->name : $request->email;
            foreach ($container as $student) {
                $myItem = '';
                $my_student_item = '';
                $student_item = $item == $request->name ?  str_split($student->first_name, 1) : str_split($student->email, 1);
                for ($i = 0; $i < strlen($item); $i++) {
                    $myItem .= strtolower($item[$i]);
                    $my_student_item .= strtolower($student_item[$i]);
                    if ($myItem  == $my_student_item) {
                        if (!in_array($student, $this->search_array)) {
                            array_push($this->search_array, $student);
                        }
                    } else {
                        if (($key = array_search($student, $this->search_array)) !== false) {
                            unset($this->search_array[$key]);
                        }
                    }
                }
            }
            $this->list_of_students = [];
            foreach ($this->search_array as $value) {
                $this->list_of_students[] = $value;
            }
        }
        return $this->list_of_students;
    }

    public function index($courses_id = null)
    {
        if (!$courses_id) {
            $students = Student::paginate();
        } else {
            foreach ($courses_id as $course_id) {
                $students[] = Student::whereHas('courses', function ($q) use ($course_id) {
                    $q->where('courses.id', $course_id);
                })->get();
            }
        }

        return $this->returnData("All Students", $students);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $asse_array = [];
        $this->authorize('student-search', $id);
        $students = Student::find($id);

        $student = Student::query()->with(['courses.modules.assessments', 'courses.assessments' => function ($q) {
            $q->where('module_id', null);
        }])->find($id);

        if (!$student) {
            return $this->returnError("Student not found", 404);
        }
        foreach ($student->courses as $course) {
            $total_assessments = $course->assessments->count() ? $course->assessments->count() : 1;
            $finished = StudentAssessment::whereIn('assessment_id', $course->assessments->pluck('id'))->get();
            $course->pivot->assess_progress = ($finished->count() / $total_assessments)  * 100;
        }

        return $this->returnData('My Courses', $student);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function filterFunc($input, $course_ignore, $course_id)
    {

        $field = isset($input->grade) ? 'grade' : 'class';
        if ($this->list_of_students) {
            foreach ($this->list_of_students as $student) {
                foreach ($input->$field as $value) {
                    if ($student->$field == $value)
                        $this->student_filter[] =  $student;
                }
            }
            $this->list_of_students =  $this->student_filter;
        } else {
            foreach ($input->$field as $value) {
                $students = Student::query()->where($field, $value)
                    ->when($course_ignore ?? false, function ($q, $course_ignore) {
                        $q->whereDoesntHave('courses', function ($q) use ($course_ignore) {
                            $q->where('course_id', $course_ignore);
                        });
                    })->when($course_id ?? false, function ($q, $course_id) {
                        $q->whereHas('courses', function ($q) use ($course_id) {
                            $q->whereIn('course_id', $course_id);
                        });
                    })->get();

                foreach ($students as $student) {
                    if (!in_array($student, $this->list_of_students)) {
                        $this->list_of_students[] =  $student;
                    }
                }
            }
        }
    }
}
