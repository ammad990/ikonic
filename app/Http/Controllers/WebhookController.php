<?php

namespace App\Http\Controllers;

use App\Services\AffiliateService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Pass the necessary data to the process order method
     * 
     * @param  Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        // TODO: Complete this method
        $orderId = $request->get('order_id');
        $subtotalPrice = $request->get('subtotal_price');
        $merchantDomain = $request->get('merchant_domain');
        $discountCode = $request->get('discount_code');
        $data = [
            'order_id' => $orderId,
            'subtotal_price' => round($subtotalPrice, 2),
            'merchant_domain' => $merchantDomain,
            'discount_code' => $discountCode
        ];
        $this->orderService->processOrder($data);
        return response()->json([
            'success' => true,
        ],200);
    }
}
