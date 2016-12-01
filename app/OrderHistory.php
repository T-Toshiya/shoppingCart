<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    //order_historiesにアクセスする
    protected $fillable = ['orderNum', 'userId'];
    
    public function orderDetails() {
        return $this->hasMany('App\OrderDetail', 'orderNum');
    }
}
