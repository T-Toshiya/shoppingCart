<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    //
    protected $fillable = ['orderNum', 'productId', 'orderQuantity'];
    
    public function orderHistories() {
        return $this->belongsTo('App\OrderHistory', 'orderNum');
    }
}
