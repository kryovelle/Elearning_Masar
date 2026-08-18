<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Module;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use function PHPUnit\Framework\returnArgument;


class TeacherCoursesController extends Controller
{
    function getTeacherCourses(Request $request){
        $teacher=$request->user();
        $query=Course::orderByDesc('created_at');

        if($request->filled('title')){
        $query->where('title','like',"%{$request->title}%");
        }

        if($request->filled('status')){
            $query->where('status',$request->status);
        }
        $courses = $query->get();
        foreach($courses as $course){
            $course->students_count=Enrollment::where('course_id',$course->id)->where('status','approved')->count();
        };
        return response()->json(['courses'=>$courses]);
    }

    function createCourse(Request $request){
            $teacher = $request->user();

             $request->validate([
                'course'=>'required|array',
                'course.title' => 'required|string|max:255',
                'course.description' => 'required|string',
                'course.price' => 'required|numeric|min:0',
                'course.featured' => 'nullable|boolean',
                'course.duration_weeks' => 'required|integer|min:1',
                'course.cover_image' => 'required|file|image|mimes:jpeg,jpg,png,webp|max:2048',
                'course.ccp_number_override' => 'nullable|string|max:255',
                'course.ccp_name_override' => 'nullable|string|max:255',
            ]);

            $reqCourse =$request->course;
            $coverImageUrl = null;

                if (isset($reqCourse['cover_image'])) {
                    $path = $reqCourse['cover_image']->store('courses/covers', 'public');
                    $coverImageUrl = Storage::url($path);
                }

                $course = Course::create([
                    'teacher_id' => $teacher->id,
                    'title' => $reqCourse['title'],
                    'status'=>'draft',
                    'description' => $reqCourse['description'],
                    'price' => $reqCourse['price'],
                    'featured' => $reqCourse['featured'] ?? true,
                    'duration_weeks' => $reqCourse['duration_weeks'],
                    'cover_image_url' => $coverImageUrl,
                    'ccp_number_override' => $reqCourse['ccp_number_override'] ?? null,
                    'ccp_name_override' => $reqCourse['ccp_name_override'] ?? null,
                ]);
            return response()->json([
                'successCreateCourse' => "Course created Succesfully",
                'course_id' => $course->id,
            ]);
        }

        private function storeLessonContent(string $type, $content): string
        {
            $rules = match ($type) {
                'video' => ['content' => 'file|mimes:mp4,mov,avi,mkv|max:102400'], // 100MB
                'pdf'   => ['content' => 'file|mimes:pdf|max:10240'],              // 10MB
                'text'  => ['content' => 'string|max:20000'],
            };

            $validator = Validator::make(['content' => $content], $rules);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            if ($type === 'text') {
                return $content;
            }

            $folder = $type === 'video' ? 'lessons/videos' : 'lessons/pdfs';
            $path = $content->store($folder, 'public');

            return Storage::url($path);
        }
    


   function editCourse(Request $request){
    $validated = $request->validate([
        'course' => 'required|array',
        'course.id' => 'required|integer|exists:courses,id',
        'course.title' => 'required|string|max:255',
        'course.description' => 'required|string',
        'course.price' => 'required|numeric|min:0',
        'course.featured' => 'nullable|boolean',
        'course.duration_weeks' => 'required|integer|min:1',
        'course.status' => 'required|string|in:draft,published,archived',
        'course.cover_image' => 'nullable|file|image|mimes:jpeg,jpg,png,webp|max:2048',
        'course.ccp_number_override' => 'nullable|string|max:255',
        'course.ccp_name_override' => 'nullable|string|max:255',

        'modules' => 'nullable|array',
        'modules.*.id' => 'nullable|integer|exists:modules,id',
        'modules.*.title' => 'required|string|max:255',
        'modules.*.order_index' => 'nullable|integer',
        'modules.*.lessons' => 'nullable|array',
        'modules.*.lessons.*.id' => 'nullable|integer|exists:lessons,id',
        'modules.*.lessons.*.title' => 'required|string|max:255',
        'modules.*.lessons.*.type' => 'required|string|in:text,video,pdf',
        'modules.*.lessons.*.order_index' => 'nullable|integer',
        'modules.*.lessons.*.content' => 'nullable',

        'deleted_modules' => 'nullable|array',
        'deleted_modules.*' => 'integer|exists:modules,id',
        'deleted_lessons' => 'nullable|array',
        'deleted_lessons.*' => 'integer|exists:lessons,id',
    ]);

    try {
        $reqCourse = $validated['course'];
        $modules = $validated['modules'] ?? [];
        $deletedModuleIds = $validated['deleted_modules'] ?? [];
        $deletedLessonIds = $validated['deleted_lessons'] ?? [];

        $course = DB::transaction(function () use ($reqCourse, $modules, $deletedModuleIds, $deletedLessonIds) {

            $course = Course::findOrFail($reqCourse['id']);

            // --- Delete lessons first (children before parents) ---
            if (!empty($deletedLessonIds)) {
                Lesson::whereIn('id', $deletedLessonIds)
                    ->whereHas('module', fn($q) => $q->where('course_id', $course->id))
                    ->delete();
            }

            // --- Delete modules (cascades their remaining lessons) ---
            if (!empty($deletedModuleIds)) {
                $modulesToDelete = Module::whereIn('id', $deletedModuleIds)
                    ->where('course_id', $course->id)
                    ->get();

                foreach ($modulesToDelete as $modToDelete) {
                    Lesson::where('module_id', $modToDelete->id)->delete();
                    $modToDelete->delete();
                }
            }

            // --- Update course ---
            $updateData = [
                'title' => $reqCourse['title'],
                'description' => $reqCourse['description'],
                'price' => $reqCourse['price'],
                'featured' => $reqCourse['featured'] ?? false,
                'duration_weeks' => $reqCourse['duration_weeks'],
                'status' => $reqCourse['status'],
                'ccp_number_override' => $reqCourse['ccp_number_override'] ?? null,
                'ccp_name_override' => $reqCourse['ccp_name_override'] ?? null,
            ];

            if (isset($reqCourse['cover_image'])) {
                $path = $reqCourse['cover_image']->store('courses/covers', 'public');
                $updateData['cover_image_url'] = Storage::url($path);
            }   

            $course->update($updateData);

            // --- Upsert modules + lessons ---
            foreach ($modules as $moduleIndex => $module) {
                if (!empty($module['id'])) {
                    $m = Module::where('id', $module['id'])
                        ->where('course_id', $course->id)
                        ->firstOrFail();

                    $m->update([
                        'title' => $module['title'],
                        'order_index' => $module['order_index'] ?? $moduleIndex + 1,
                    ]);
                } else {
                    $m = Module::create([
                        'course_id' => $course->id,
                        'title' => $module['title'],
                        'order_index' => $module['order_index'] ?? $moduleIndex + 1,
                    ]);
                }

                $lessons = $module['lessons'] ?? [];
                foreach ($lessons as $lessonIndex => $lesson) {
                    $lessonData = [
                        'title' => $lesson['title'],
                        'type' => $lesson['type'],
                        'order_index' => $lesson['order_index'] ?? $lessonIndex + 1,
                    ];

                    if (isset($lesson['content'])) {
                        $lessonData['content_url'] = $this->storeLessonContent($lesson['type'], $lesson['content']);
                    }

                    if (!empty($lesson['id'])) {
                        $les = Lesson::where('id', $lesson['id'])
                            ->where('module_id', $m->id)
                            ->firstOrFail();

                        $les->update($lessonData);
                    } else {
                        // new lesson must have content (text/video/pdf) on creation
                        if (!isset($lesson['content'])) {
                            throw ValidationException::withMessages([
                                'modules' => 'New lessons must include content.',
                            ]);
                        }

                        Lesson::create(array_merge($lessonData, [
                            'module_id' => $m->id,
                        ]));
                    }
                }
            }

            return $course;
        });

        return response()->json(['successEditCourse' => 'Course updated successfully!']);

    } catch (ValidationException $e) {
        throw $e;
    } catch (\Throwable $e) {
        report($e);
        return response()->json(['failedEditCourse' => 'An Error occurred, Please try again!'], 500);
    }
}
    
   function getCourseById(Request $request){
    $request->validate([
        'id' => 'required|integer|exists:courses,id',
    ]);

    $course = Course::with('modules.lessons')->find($request->id);

    if (!$course) {
        return response()->json(['CourseNotFound' => 'Course not found'], 404);
    }

    return response()->json([
        'course' => $course,
    ]);
}
}


