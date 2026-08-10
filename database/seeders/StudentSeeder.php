<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::query()->where('code', 'DEMO')->firstOrFail();
        $academicYear = AcademicYear::query()->where('school_id', $school->id)->where('is_active', true)->firstOrFail();
        $classSections = ClassSection::query()->where('school_id', $school->id)->get();

        app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);

        $names = [
            ['first_name' => 'Arjun', 'last_name' => 'Verma'],
            ['first_name' => 'Priya', 'last_name' => 'Patel'],
            ['first_name' => 'Rohit', 'last_name' => 'Sharma'],
            ['first_name' => 'Sneha', 'last_name' => 'Reddy'],
            ['first_name' => 'Amit', 'last_name' => 'Singh'],
            ['first_name' => 'Neha', 'last_name' => 'Gupta'],
            ['first_name' => 'Vikram', 'last_name' => 'Joshi'],
            ['first_name' => 'Pooja', 'last_name' => 'Nair'],
            ['first_name' => 'Karan', 'last_name' => 'Mehta'],
            ['first_name' => 'Divya', 'last_name' => 'Kapoor'],
            ['first_name' => 'Ravi', 'last_name' => 'Desai'],
            ['first_name' => 'Anjali', 'last_name' => 'Menon'],
            ['first_name' => 'Kunal', 'last_name' => 'Rane'],
            ['first_name' => 'Isha', 'last_name' => 'Bhatt'],
            ['first_name' => 'Sahil', 'last_name' => 'Malhotra'],
            ['first_name' => 'Riya', 'last_name' => 'Choudhary'],
            ['first_name' => 'Manish', 'last_name' => 'Yadav'],
            ['first_name' => 'Tanvi', 'last_name' => 'Kulkarni'],
            ['first_name' => 'Aditya', 'last_name' => 'Saxena'],
            ['first_name' => 'Meera', 'last_name' => 'Iyer'],
            ['first_name' => 'Nikhil', 'last_name' => 'Bose'],
            ['first_name' => 'Kavya', 'last_name' => 'Mishra'],
            ['first_name' => 'Siddharth', 'last_name' => 'Pillai'],
            ['first_name' => 'Aarohi', 'last_name' => 'Trivedi'],
            ['first_name' => 'Rahul', 'last_name' => 'Chawla'],
            ['first_name' => 'Nandini', 'last_name' => 'Rao'],
            ['first_name' => 'Gaurav', 'last_name' => 'Aggarwal'],
            ['first_name' => 'Sana', 'last_name' => 'Sheikh'],
            ['first_name' => 'Yash', 'last_name' => 'Thakur'],
            ['first_name' => 'Ira', 'last_name' => 'Dutta'],
            ['first_name' => 'Aryan', 'last_name' => 'Bajwa'],
            ['first_name' => 'Tara', 'last_name' => 'Sundaram'],
            ['first_name' => 'Devansh', 'last_name' => 'Kohli'],
            ['first_name' => 'Maya', 'last_name' => 'Nayar'],
            ['first_name' => 'Harsh', 'last_name' => 'Gill'],
            ['first_name' => 'Ritika', 'last_name' => 'Bal'],
            ['first_name' => 'Ishaan', 'last_name' => 'Agarwal'],
            ['first_name' => 'Zara', 'last_name' => 'Khan'],
            ['first_name' => 'Om', 'last_name' => 'Sethi'],
            ['first_name' => 'Ananya', 'last_name' => 'Das'],
        ];

        foreach ($names as $index => $data) {
            $student = Student::factory()->create([
                'school_id' => $school->id,
                'admission_no' => 'ADM'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
            ]);

            $student->sessions()->create([
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id,
                'class_section_id' => $classSections[$index % $classSections->count()]->id,
                'roll_no' => (string) ($index + 1),
                'joined_on' => $student->admission_date,
                'status' => 'active',
            ]);

            $student->guardians()->create([
                'school_id' => $school->id,
                'relation' => $index % 2 === 0 ? 'Father' : 'Mother',
                'name' => fake()->name('male'),
                'phone' => fake()->phoneNumber(),
                'email' => fake()->safeEmail(),
                'occupation' => fake()->jobTitle(),
                'is_primary' => true,
                'can_pickup' => true,
            ]);

            $email = 'student.'.strtolower($data['first_name']).'.'.strtolower($data['last_name']).'@example.com';

            $user = User::factory()->create([
                'uuid' => (string) Str::uuid(),
                'name' => $data['first_name'].' '.$data['last_name'],
                'email' => $email,
                'password' => Hash::make('password'),
                'current_school_id' => $school->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
            $user->assignRole('Student');
            $user->schools()->syncWithoutDetaching([$school->id => ['status' => 'active', 'is_primary' => true]]);

            $student->update(['user_id' => $user->id]);
        }
    }
}