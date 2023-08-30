<?php

namespace App\Services;

use App\Models\Market;
use App\Models\Order;
use App\Models\Product;
use App\Models\SubscriptionPackage;
use Illuminate\Support\Collection;

class ProductWiseOrderService
{
    public function fetchBaseOrderForSubOrder(Collection $data)
    {
        $data['order_category'] = Order::PRODUCT_BASED;
        $data['distance'] = null;
        return $data;
    }

    public function fetchMutatedRequestForSubOrder(Collection $data, Market $market, $marketCount, $deliveryLat, $deliveryLon)
    {
        $data['order_category'] = Order::VENDOR_BASED;
        $products = [];
        $tax = 0.0;
        $subTotal = 0.0;

        //creating order data with products belongs to market_id
        if ($data['products'] && is_array($data['products']) && count($data['products']) > 0) {
            foreach ($data['products'] as $product) {
                $product_data = Product::findOrFail($product['product_id']);
                if ($product_data->market_id == $market->id) {
                    info("PRODUCT : " . json_encode($product));
                    $products[] = $product;
                    $tax += $product_data->tax;
                    $subTotal += $product_data->price;
                }
            }

            $data['products'] = $products;
            $data['tax'] = $tax;
            $data['delivery_fee'] = $data['delivery_fee'] > 0 ? $data['delivery_fee'] / $marketCount : 0; //$data['delivery_fee'] passed should be total delivery fee
            $data['sub_total'] = $subTotal;
            $data['total_amount'] = $data['delivery_fee'] + $subTotal;

            //distance calculate
            $distance = $this->calculateDistance($market->latitude, $market->longitude, $deliveryLat, $deliveryLon);
            $data['distance'] = $distance;
        }

        return $data;
    }

    public function calculateDistance($startLatitude, $startLongitude, $endLatitude, $endLongitude)
    {

        $earthRadius = 6371; // Radius of the Earth in kilometers

// Convert degrees to radians
        $startLatitudeRad = deg2rad($startLatitude);
        $startLongitudeRad = deg2rad($startLongitude);
        $endLatitudeRad = deg2rad($endLatitude);
        $endLongitudeRad = deg2rad($endLongitude);

// Calculate the differences between the coordinates
        $latitudeDiff = $endLatitudeRad - $startLatitudeRad;
        $longitudeDiff = $endLongitudeRad - $startLongitudeRad;

// Calculate the distance using the Haversine formula
        $a = sin($latitudeDiff / 2) * sin($latitudeDiff / 2) + cos($startLatitudeRad) * cos($endLatitudeRad) * sin($longitudeDiff / 2) * sin($longitudeDiff / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}