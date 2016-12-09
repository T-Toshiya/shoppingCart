<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Blade::directive('header', function() {
            return '<?php header("Content-Type: application/xml"); ?>';
        });
        
        Blade::directive('amazon', function($result) {
            return '<?php var_dump($result); ?>';
        });
        
        Blade::directive('orderTime', function($orderDetail) {
            return '<?php $time = strtotime($orderDetail[0]->created_at); $time = getdate($time); echo $time["year"]."年".$time["mon"]."月".$time["mday"]."日"; ?>';
        });
        
        Blade::directive('orderNum', function($product) {
            return '<?php $orderNum = $product->productNum; for ($i = 1; $i <= 10; $i++) { echo "<p>$i</p>"; }  ?>';
        });
        
        Blade::directive('count', function() {
            return '<?php $count = 0; ?>';
        });
        
        Blade::directive('orderGroup', function($product) {
            return '<?php if ($count == 0) { echo "<p>購入日:$product->created_at</p>"; $preOrderTime = $product->created_at; $count++; } else { if ($product->created_at != $preOrderTime) { echo "<hr color=\"red\" size=\"5\"><p>購入日:$product->created_at</p>"; } $preOrderTime = $product->created_at; }  ?>';
        });
        
        
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
