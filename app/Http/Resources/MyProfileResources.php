<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyProfileResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'birthdate' => $this->birthdate,
            'weight' => $this->weight,
            'height' => $this->height,
            'gender' => ($this->gender == '1') ? 'Male' : 'Female',
            'image' => $this->image,
            'age' => Carbon::parse($this->birthdate)->age,
        ];
    }
}
