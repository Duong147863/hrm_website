<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WorkingProcessesResource extends JsonResource
{
    public function toArray($request)
    {
        return
            [
                'workingprocesses_id' => $this->workingprocesses_id,
                'profile_id' => $this->profile_id,
                'workingprocesses_content' => $this->workingprocesses_content,
                'start_time' => $this->start_time->format('d/m/Y'),
                // 'end_time' => $this->end_time->format('d/m/Y'),
                'end_time' => $this->end_time ? $this->end_time->format('d/m/Y') : null, // Kiểm tra null
                'workingprocesses_status' => $this->workingprocesses_status,
                'workplace_name' => $this->workplace_name,
            ];
    }
}