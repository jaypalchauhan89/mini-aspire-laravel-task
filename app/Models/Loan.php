<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Loan extends Model
{
    use HasFactory;
  
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = "loans";
    protected $guarded = ['id'];
    



    public function getStatusAttribute($value)
    {
        $labelHtml=$value;
        if($value==0){
            $labelHtml = 'PENDING';
        }elseif($value==1){
            $labelHtml = 'APPROVED';
        }elseif($value==2){
            $labelHtml = 'PAID';
        }   
        
        return $labelHtml;
    }

    

    public function isPending()
    {
        $value = $this->attributes['status'];
        if($value==0){
            return true;
        }
        return false;
    } 

    public function isApproved()
    {
        $value = $this->attributes['status'];
        if($value==1){
            return true;
        }
        return false;
    }


    public function isPaid()
    {
        $value = $this->attributes['status'];
        if($value==2){
            return true;
        }
        return false;
    } 

    public function payments(){
      return $this->hasMany('App\Models\LoanPayment','loan_id');
    }

}
