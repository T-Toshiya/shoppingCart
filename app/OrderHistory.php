<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    //order_historiesにアクセスする
    protected $fillable = ['userName', 'productName', 'totalNum', 'totalMoney', 'imagePath'];
}
