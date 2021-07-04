<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoansRepaymentTracking extends Model
{
    use HasFactory;

    // changed the default id to uid
    protected $primaryKey = 'loan_repay_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'loan_id',
        'repay_amount',
    ];
}
