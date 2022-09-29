<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Loan;
use App\Models\LoanPayment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Traits\ApiResponser;

class LoanController extends Controller
{
    use ApiResponser;
    /**
     * Loan List
     * @param Request $request
     * @return Loans
     */
    public function index(Request $request)
    {
        $records = Loan::where('created_by',Auth::id())->orderBy('id','DESC')->get()->toArray();
        return $this->successResponse($records,'Loan list',200);
        
    }


    /**
     * Loan detail
     * @param Request $request
     * @return Loan detail
     */
    public function detail($loanId,Request $request)
    {
        $authId = Auth::id();
        $record = Loan::with(['payments'=>function($q){
            $q->select('id','loan_id','emi_amount','paid_amount','emi_date','paid_date','status');
        }])->where('id',$loanId)->where('created_by',$authId)->first();
        if(empty($record)){
            return $this->successResponse([],__('error.no_record_found'),200);
        }
        return $this->successResponse([$record],'Loan Detail',200);
    }


    /**
     * Apply loan
     * @param Request $request
     * @return json
     */
    public function applyLoan(Request $request)
    {
        
        $validateUser = Validator::make($request->all(), 
        [
            'loan_amount' => 'required|numeric',
            'loan_term' => 'required|integer',
            'title'=>'required|string|max:255',
        ]);

        if($validateUser->fails()){
            return $this->errorResponse('validation error',$validateUser->errors(),422);
        }
        
        $applicationDate = Carbon::now()->format('Y-m-d');
        $loan = Loan::create([
            'loan_title'=>$request->title,  
            'loan_amount'=>$request->loan_amount, 
            'loan_term'=>$request->loan_term,   
            'application_date'=>$applicationDate,
            'created_by'=>Auth::id(),    
        ]);

        return $this->successResponse([$loan],'Loan applied successfully.',201);
        
    }

    /**
     * loan repayment
     * @param Request $request
     * @return json
     */
    public function repayment($loanId,Request $request)
    {

        $validateUser = Validator::make($request->all(), 
        [
            'amount' => 'required|numeric|min:1',
        ]);

        if($validateUser->fails()){
            return $this->errorResponse('validation error',$validateUser->errors(),422);
        }

        $authId = Auth::id();
        $record = Loan::where('id',$loanId)->where('created_by',$authId)->first();
        if(empty($record)){
            return $this->successResponse([],'No loan found',200);
        }

        if($record->isPaid()){
            return $this->errorResponse('Loan Already paid',[],200);
        }

        $pendingPayments = $record->payments()->pending()->orderBy('emi_date','ASC')->get();
        $paymentAmount = $request->amount;
       
        $counter = 0;
        foreach($pendingPayments as $pendingPayment){
            if($paymentAmount<=0){
                continue;
            }

            $emiAmount = $pendingPayment->emi_amount;
            if(!empty($pendingPayment->paid_amount)){
                $emiAmount = $pendingPayment->emi_amount - $pendingPayment->paid_amount;
            }

            if($counter==$pendingPayments->count()-1){
                if(!empty($pendingPayment->paid_amount)){
                    $pendingPayment->paid_amount = $pendingPayment->paid_amount + $paymentAmount;
                }else{
                    $pendingPayment->paid_amount = $paymentAmount;
                }
            }else{
                if($emiAmount>=$paymentAmount){
                    $pendingPayment->paid_amount = $paymentAmount;
                }else{
                    $pendingPayment->paid_amount = $pendingPayment->emi_amount;//$emiAmount;
                }
            }

            $paymentAmount = $paymentAmount - $emiAmount;    
            $pendingPayment->update();
            $counter++;

        }
        
        $pendingPayments =$record->payments()->pending()->get();
        foreach($pendingPayments as $pendingPayment){
            if(!empty($pendingPayment->paid_amount) && $pendingPayment->paid_amount>=$pendingPayment->emi_amount){
                $pendingPayment->status=1;
                $pendingPayment->paid_date=Carbon::now();
                $pendingPayment->update();
            }
        }

        //check if all payment are made or not
        $pendingPayments =$record->payments()->pending()->get();
        if(!$pendingPayments->count()){
            $record->status=2;
            $record->update();
        }

        return $this->successResponse([],'Payment Successfully.',201);

    }
    


    /**
     * term repayment
     * @param Request $request
     * @return json
     */
    public function repaymentAgainstEMI($loanId,$emiId,Request $request)
    {
        $validateUser = Validator::make($request->all(), 
        [
            'amount' => 'required|numeric|min:1',
        ]);

        if($validateUser->fails()){
            return $this->errorResponse('validation error',$validateUser->errors(),422);
        }

        $authId = Auth::id();
        $record = Loan::where('id',$loanId)->where('created_by',$authId)->first();
        if(empty($record)){
            return $this->successResponse([],'No loan found',200);
        }

        if($record->isPaid()){
            return $this->errorResponse('Loan Already paid',[],200);
        }

        $emiRecord = LoanPayment::where([
            'id'=>$emiId,
            'loan_id'=>$loanId,
            'user_id'=>$authId,
        ])->first();
        
        if(empty($emiRecord)){
            return $this->successResponse([],'No EMI Detail found',200);
        }

        if($emiRecord->isPaid()){
            return $this->errorResponse('EMI Already paid',[],200);
        }
        $paymentAmount = $request->amount;
        
        if($paymentAmount<$emiRecord->emi_amount){
            return $this->errorResponse(__('error.payment_amount_must_be_greator_or_same'),[],422);
        }

        if(!empty($emiRecord->paid_amount)){
           $emiRecord->paid_amount = $paymentAmount+$emiRecord->paid_amount;
        }else{
            $emiRecord->paid_amount = $paymentAmount;
        }

        
        if($emiRecord->paid_amount>=$emiRecord->emi_amount){
            $emiRecord->status=1;
        }
        $emiRecord->paid_date = Carbon::now();
        $emiRecord->update();


        //check if all payment are made or not
        $pendingPayments =$record->payments()->pending()->get();
        if(!$pendingPayments->count()){
            $record->status=2;
            $record->update();
        }
        return $this->successResponse([],'Payment Successfully.',201);
    }
}