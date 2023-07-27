<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // TODO: Complete this method
        $merchant = Merchant::where('domain',$data['merchant_domain'])->first();
        
        $user = User::where('email', $data['customer_email'])->where('type',User::TYPE_AFFILIATE)->first();
        if(empty($user)){
            $user = User::create([
                'name'      => $data['customer_name'],
                'email'     => $data['customer_email'],
                'type'      => User::TYPE_AFFILIATE
            ]);
        }
       
        $aff = new Affiliate(
        [
            'user_id'        => $user->id,
            'merchant_id'    => $merchant->id,
            'discount_code'  => $data["discount_code"],
            'commission_rate'=> $merchant->default_commission_rate
        ]);
        $user->affiliate()->save($aff);
        $user->refresh();

        $existingOrder = Order::where('id', $data['order_id'])->where('external_order_id', $data['order_id'])->first();
        if(empty($existingOrder)){
            $subtotal = $data['subtotal_price'];
            $commission_rate =  $merchant->default_commission_rate;
            $commission_owed = $subtotal * ($commission_rate / 100);
            
            $order = Order::create([
                'subtotal'      => $data['subtotal_price'],
                'merchant_id'     => $merchant->id,
                'affiliate_id'  => $user->affiliate->id,
                'commission_owed'      => round($commission_owed, 2),
                'discount_code'      => $data["discount_code"],
            ]);
            //Log::info("Commission Owed: ".$commission_owed);
        }
        else
        {
        }

    }
}
