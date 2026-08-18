<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Payment;
use App\Models\SocialLinks;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        //social links
        $social_link=SocialLinks::create([
            'created_at'=>now(),
            'facebook'=>'salma_salam',
            'instagram'=>'salma_salam',
            'whatsapp'=>'salma_salam',
            'email'=>'salma_salam2548@gmail.com'
        ]);
        // ---------------------------------------------------------
        // 1. Teacher account — fixed credentials for immediate login
        // ---------------------------------------------------------
        $teacher = User::create([
            'name' => 'Salma Salam',
            'email' => 'teacher@masar.test',
            'password' => Hash::make('Teacher@123'),
            'phone' => '0555123456',
            'photo_url'=>'/storage/ui/home-page-avatar.jpg',
            'role' => 'teacher',
            'bio' => 'Math & Physics teacher with 10+ years of experience.',
            'ccp_number' => '123456789',
            'ccp_name' => 'Salma Salam',
            'email_verified_at' => now(),

        ]);

        // ---------------------------------------------------------
        // 2. Students — 8 total, Faker-generated
        // ---------------------------------------------------------
        $students = User::factory()->count(8)->create([
            'role' => 'student',
            'password' => Hash::make('Student@123'),
            'phone'=>'11111111111',
            'email_verified_at' => now(),
        ]);

        // ---------------------------------------------------------
        // 3. Courses + Modules + Lessons (with order_index)
        // ---------------------------------------------------------
        $coursesData = [
            [
                'title' => 'Algebra Fundamentals',
                'description' => 'A solid foundation in algebra — variables, linear equations, and quadratics, built step by step with worked examples.',
                'price' => 3000,
                'duration_weeks' => 8,
                'featured'=>1,
                'status' => 'published',
                'cover' => 'courses/covers/algebra-cover.jpg',
                'modules' => [
                    [
                        'title' => 'Linear Equations',
                        'order_index' => 1,
                        'lessons' => [
                            ['title' => 'Introduction to Variables', 'type' => 'text', 'content' => "A variable is a symbol, usually a letter, that represents an unknown or changeable value. In algebra, we use variables to write general rules that work for many numbers at once.\n\nFor example, in the equation x + 5 = 12, the letter x represents the unknown number we need to find.", 'order_index' => 1],
                            ['title' => 'Practice Sheet — Linear Equations', 'type' => 'pdf', 'path' => 'lessons/pdfs/linear-equations-practice.pdf', 'order_index' => 2],
                        ],
                    ],
                    [
                        'title' => 'Quadratic Equations',
                        'order_index' => 2,
                        'lessons' => [
                            ['title' => 'Factoring Quadratics', 'type' => 'video', 'path' => 'lessons/videos/factoring-quadratics.mp4', 'order_index' => 1],
                            ['title' => 'The Quadratic Formula — Reference Sheet', 'type' => 'pdf', 'path' => 'lessons/pdfs/quadratic-formula-sheet.pdf', 'order_index' => 2],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Introduction to Physics',
                'description' => 'A beginner-friendly introduction to the fundamental concepts of physics.',
                'price' => 2500,
                'featured'=>1,
                'duration_weeks' => 6,
                'status' => 'published',
                'cover' => 'courses/covers/physics-intro-cover.jpg',
                'modules' => [
                    [
                        'title' => 'Module 1: Motion Basics',
                        'order_index' => 1,
                        'lessons' => [
                            [
                                'title' => 'What is Motion?',
                                'type' => 'text',
                                'content' => "Motion is the change in position of an object over time. Everything in the universe is in motion. In physics, we describe motion using three key quantities: Position, Velocity, and Acceleration.",
                                'order_index' => 1
                            ],
                            [
                                'title' => 'Distance vs Displacement',
                                'type' => 'text',
                                'content' => "Distance is the total length of the path traveled. Displacement is the straight-line distance from start to end, including direction. Distance is a scalar, displacement is a vector.",
                                'order_index' => 2
                            ]
                        ]
                    ]
                ]
                ],
            [
                'title' => 'Mechanics: Forces & Motion',
                'description' => 'Explore kinematics, Newton\'s laws, and energy conservation through demonstrations and guided problem-solving.',
                'price' => 4500,
                'featured'=>1,
                'duration_weeks' => 10,
                'status' => 'published',
                'cover' => 'courses/covers/mechanics-cover.jpg',
                'modules' => [
                    [
                        'title' => 'Kinematics',
                        'order_index' => 1,
                        'lessons' => [
                            ['title' => 'Position, Velocity, Acceleration', 'type' => 'text', 'content' => "Kinematics describes motion without worrying about what causes it.\n\nPosition tells us where an object is. Velocity tells us how fast position is changing. Acceleration tells us how fast velocity is changing. Together, these three quantities let us fully describe an object's motion over time.", 'order_index' => 1],
                        ],
                    ],
                    [
                        'title' => "Newton's Laws",
                        'order_index' => 2,
                        'lessons' => [
                            ['title' => 'Force Diagrams Worksheet', 'type' => 'pdf', 'path' => 'lessons/pdfs/force-diagrams-worksheet.pdf', 'order_index' => 1],
                        ],
                    ],
                    [
                        'title' => 'Energy & Work',
                        'order_index' => 3,
                        'lessons' => [
                            ['title' => 'Kinetic vs Potential Energy', 'type' => 'text', 'content' => "Kinetic energy is the energy of motion — the faster an object moves, the more kinetic energy it has.\n\nPotential energy is stored energy based on position, such as an object held above the ground. As an object falls, potential energy converts into kinetic energy.", 'order_index' => 1],
                            ['title' => 'Energy Conservation Problems', 'type' => 'pdf', 'path' => 'lessons/pdfs/energy-conservation-problems.pdf', 'order_index' => 2],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Calculus I: Limits & Derivatives',
                'description' => 'An introduction to calculus, starting with limits and building up to derivatives and rates of change.',
                'price' => 5000,
                'duration_weeks' => 12,
                'featured'=>1,
                'status' => 'published',
                'cover' => 'courses/covers/calculus-cover.jpg',
                'modules' => [
                    [
                        'title' => 'Limits',
                        'order_index' => 1,
                        'lessons' => [
                            ['title' => 'What Is a Limit?', 'type' => 'text', 'content' => "A limit describes the value a function approaches as its input gets closer and closer to some point — even if the function is never actually defined at that exact point.\n\nLimits are the foundation on which the rest of calculus, including derivatives, is built.", 'order_index' => 1],
                        ],
                    ],
                    [
                        'title' => 'Derivatives',
                        'order_index' => 2,
                        'lessons' => [
                            ['title' => 'Derivative Rules Cheat Sheet', 'type' => 'pdf', 'path' => 'lessons/pdfs/derivative-rules-cheatsheet.pdf', 'order_index' => 1],
                        ],
                    ],
                ],
            ],
        ];

        $createdCourses = [];

        foreach ($coursesData as $courseIndex => $courseData) {
            $coverUrl = Storage::disk('public')->exists($courseData['cover'])
                ? Storage::url($courseData['cover'])
                : null;

            $course = Course::create([
                'teacher_id' => $teacher->id,
                'title' => $courseData['title'],
                'description' => $courseData['description'],
                'price' => $courseData['price'],
                'featured' =>  $courseData['featured'],
                'duration_weeks' => $courseData['duration_weeks'],
                'status' => $courseData['status'],
                'cover_image_url' => $coverUrl,
            ]);

            $createdCourses[] = $course;

            foreach ($courseData['modules'] as $moduleData) {
                $module = Module::create([
                    'course_id' => $course->id,
                    'title' => $moduleData['title'],
                    'order_index' => $moduleData['order_index'],
                ]);

                foreach ($moduleData['lessons'] as $lessonData) {
                    if ($lessonData['type'] === 'text') {
                        $contentUrl = $lessonData['content'];
                    } else {
                        $contentUrl = Storage::disk('public')->exists($lessonData['path'])
                            ? Storage::url($lessonData['path'])
                            : null;
                    }

                    Lesson::create([
                        'module_id' => $module->id,
                        'title' => $lessonData['title'],
                        'type' => $lessonData['type'],
                        'content_url' => $contentUrl,
                        'order_index' => $lessonData['order_index'],
                    ]);
                }
            }
        }

        // ---------------------------------------------------------
        // 4. Enrollments + Payments
        // ---------------------------------------------------------
        $publishedCourses = array_filter($createdCourses, fn($c) => $c->status === 'published');
        $publishedCourses = array_values($publishedCourses);
        $studentsArr = $students->values();

        $receiptPaths = [
            'receipts/receipt-01.jpg',
            'receipts/receipt-02.jpg',
            'receipts/receipt-03.jpg',
        ];

        $receiptUrl = function (int $index) use ($receiptPaths) {
            $path = $receiptPaths[$index % count($receiptPaths)];
            return Storage::disk('public')->exists($path)
                ? Storage::url($path)
                : null;
        };

        // 3 approved
        $approvedPairs = [
            [$studentsArr[0], $publishedCourses[0]],
            [$studentsArr[1], $publishedCourses[1]],
            [$studentsArr[2], $publishedCourses[0]],
        ];
        foreach ($approvedPairs as $i => [$student, $course]) {
            $enrollment = Enrollment::create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'status' => 'approved',
                'approved_at' => now()->subDays(rand(2, 20)),
            ]);

            Payment::create([
                'enrollment_id' => $enrollment->id,
                'amount' => $course->price,
                'receipt_image_url' => $receiptUrl($i),
                'student_note' => 'Paid via CCP transfer, reference attached.',
                'status' => 'approved',
                'submitted_at' => now()->subDays(rand(3, 21)),
                'reviewed_at' => now()->subDays(rand(1, 19)),
            ]);
        }

        // 2 pending
        $pendingPairs = [
            [$studentsArr[3], $publishedCourses[1]],
            [$studentsArr[4], $publishedCourses[0]],
        ];
        foreach ($pendingPairs as $i => [$student, $course]) {
            $enrollment = Enrollment::create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'status' => 'pending',
            ]);

            Payment::create([
                'enrollment_id' => $enrollment->id,
                'amount' => $course->price,
                'receipt_image_url' => $receiptUrl($i),
                'student_note' => 'Just made the transfer, receipt attached for review.',
                'status' => 'pending',
                'submitted_at' => now()->subDays(rand(0, 2)),
            ]);
        }

        // 1 rejected
        $rejectedStudent = $studentsArr[5];
        $rejectedCourse = $publishedCourses[1];

        $rejectedEnrollment = Enrollment::create([
            'student_id' => $rejectedStudent->id,
            'course_id' => $rejectedCourse->id,
            'status' => 'rejected',
        ]);

        Payment::create([
            'enrollment_id' => $rejectedEnrollment->id,
            'amount' => $rejectedCourse->price,
            'receipt_image_url' => $receiptUrl(2),
            'student_note' => 'Here is my receipt.',
            'teacher_note' => 'The receipt image is unclear and the amount doesn\'t match the course price. Please resubmit with a clearer photo.',
            'status' => 'rejected',
            'submitted_at' => now()->subDays(5),
            'reviewed_at' => now()->subDays(4),
        ]);


        // studentsArr[6] and studentsArr[7] intentionally get NO enrollments
    }

    //social links
}