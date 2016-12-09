<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderAmazonDetail extends Model
{
    //
    protected $fillable = ['orderNum', 'productId', 'productName', 'productPrice', 'imagePath', 'orderQuantity'];
    
    public function orderHistories() {
        return $this->belongsTo('App\OrderHistory', 'orderNum');
    }
}
