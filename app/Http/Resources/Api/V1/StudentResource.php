<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'admission_no' => $this->admission_no,
            'admission_date' => $this->admission_date?->format('Y-m-d'),
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'gender' => $this->gender,
            'blood_group' => $this->blood_group,
            'religion' => $this->religion,
            'category' => $this->category,
            'caste' => $this->caste,
            'nationality' => $this->nationality,
            'mother_tongue' => $this->mother_tongue,
            'aadhar_no' => $this->aadhar_no,
            'photo_url' => $this->photo_path ? asset('storage/' . $this->photo_path) : null,
            'current_address' => $this->current_address,
            'permanent_address' => $this->permanent_address,
            'status' => $this->status,

            'current_session' => $this->whenLoaded('sessions', function () {
                $session = $this->sessions->where('status', 'active')->first();
                if (!$session) return null;

                return [
                    'academic_year_id' => $session->academic_year_id,
                    'academic_year' => $session->academicYear?->name,
                    'class_section_id' => $session->class_section_id,
                    'class' => $session->classSection?->schoolClass?->name,
                    'section' => $session->classSection?->section?->name,
                    'roll_no' => $session->roll_no,
                    'joined_on' => $session->joined_on,
                ];
            }),

            'guardians' => StudentGuardianResource::collection($this->whenLoaded('guardians')),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}