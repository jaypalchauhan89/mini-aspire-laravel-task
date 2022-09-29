<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class LoanPayment extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = "loan_payments";
    protected $guarded = ['id'];
    

    public function scopePending($query)
    {
        $query->where('status', 0);
    }

    public function isPending()
    {
        $value = $this->attributes['status'];
        if($value==0){
            return true;
        }
        return false;
    } 

    public function isPaid()
    {
        $value = $this->attributes['status'];
        if($value==1){
            return true;
        }
        return false;
    }

    public function getStatusAttribute($value)
    {
        $labelHtml=$value;
        if($value==0){
            $labelHtml = 'PENDING';
        }elseif($value==1){
            $labelHtml = 'PAID';
        }
        return $labelHtml;
    }


    
    

}
