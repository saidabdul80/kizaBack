<?php

namespace App\Http\Resources;

use App\Services\Util;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name'=> $this->first_name, 
            'last_name'=> $this->last_name, 
            'full_name'=> $this->full_name,
            'email'=> $this->email, 
            'phone_number'=> $this->phone_number, 
            'email_verified_at'=> $this->email_verified_at, 
            'phone_number_verified_at'=> $this->phone_number_verified_at, 
            'picture_url'=>$this->picture_url? Util::publicUrl($this->picture_url):null, 
            'nin_slip_url'=> $this->nin_slip_url? Util::publicUrl($this->nin_slip_url):null, 
            'international_passport_url'=> $this->international_passport_url? Util::publicUrl($this->international_passport_url):null,
            'utility_bills_url'=>   $this->utility_bills_url? Util::publicUrl($this->utility_bills_url):null,
            'drivers_license_url'=> $this->drivers_license_url? Util::publicUrl($this->drivers_license_url):null,
            'permanent_residence_card_url'=> $this->permanent_residence_card_url? Util::publicUrl($this->permanent_residence_card_url):null,
            'proof_of_address_url'=> $this->proof_of_address_url? Util::publicUrl($this->proof_of_address_url):null,

            //'ajos' => AjoResource::collection($this->whenLoaded('ajos')),
            //'ajo_members' => AjoMemberResource::collection($this->whenLoaded('ajoMembers')),
            // 'mfa_methods' => MfaMethodResource::collection($this->whenLoaded('mfaMethods')),
            // 'user_identifications' => UserIdentificationResource::collection($this->whenLoaded('userIdentifications')),
            // 'transactions' => TransactionResource::collection($this->whenLoaded('transactions')),
            // 'wallet' => new WalletResource($this->whenLoaded('wallet')),
        ];
    }
}
