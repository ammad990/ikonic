<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // TODO: Complete this method
        $discountCode = $this->apiService->createDiscountCode($merchant);
       
        $user = User::where('email', $email)->first();
        if(empty($user)){
            $user = User::create([
                'name'      => $name,
                'email'     => $email,
                'type'      => User::TYPE_AFFILIATE
            ]);
            $aff = Affiliate::where('user_id',$user->id)->first();
            if(empty($aff)){
                $aff = Affiliate::create([
                'user_id'        => $user->id,
                'merchant_id'    => $merchant->id,
                'discount_code'  => $discountCode["code"],
                'commission_rate'=> $commissionRate
                ]);
            }
            Mail::to($email)->send(new AffiliateCreated($aff));
            return $aff;
        }
        else{
            if(empty($merchant->user->affiliate))
            {
                $aff = new Affiliate(
                [
                    'user_id'        => $merchant->user->id,
                    'merchant_id'    => $merchant->id,
                    'discount_code'  => is_string($discountCode['code'])?$discountCode['code'] : $discountCode['code']->serialize(),
                    'commission_rate'=> $commissionRate
                ]);
                $merchant->user->affiliate()->save($aff);
                $merchant->user->refresh();
                return $merchant->user->affiliate;
            }
        }
        return $user->affiliate;
    }
}
