<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'userid'       => $this->id,
            'name'         => $this->full_name,
            'avatar'       => "",
            'email'        => $this->email,
            'signature'    => "",
            'title'        => "",
            'group'        => "",
            'tags'         => [],
            'notifyCount'  => 0,
            'unreadCount'  => 0,
            'country'      => "",
            'geographic'   => [
                'province' => [
                    'label' => "",
                    'key'   => "",
                ],
                'city'     => [
                    'label' => "",
                    'key'   => "",
                ],
            ],
            'address'      => "",
            'phone'        => "",
            'role'         => $this->role,
            'permission'   => $this->getAllPermissions()->pluck('name'),
            'display_role' => $this->display_role,
            'status'       => $this->status,
        ];
    }
}
