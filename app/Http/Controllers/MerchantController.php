<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Order;
class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {}

    /**
     * Useful order statistics for the merchant API.
     * 
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        // TODO: Complete this method
        $from = $request->get('from');
        $to = $request->get('to');
        $orders = Order::whereBetween('created_at', [$from, $to])->get();
        $totalOrders = count($orders);
        $commissionsOwned = 0;
        $revenue=0;
        foreach($orders as $order){
            if($order->payout_status==Order::STATUS_UNPAID && !empty($order->affiliate_id) )
            {
                $commissionsOwned += $order->commission_owed;
            }
            $revenue+= $order->subtotal;
        }

        return response()->json([
            'count' => $totalOrders,
            'commissions_owed'=>$commissionsOwned,
            'revenue'=>$revenue
        ]);     
    }
}
