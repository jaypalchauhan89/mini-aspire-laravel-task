<?php

namespace App\Http\Controllers\Api\Admin;

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
        
        $records = Loan::with('payments')->orderBy('id','DESC')->get()->toArray();
        return $this->successResponse($records,'Loan list',200);
    }

    /**
     * Approve Loan
     * @param Request $request
     * @return Loans
     */
    public function approveLoan($loanId,Request $request)
    {
        
        $record = Loan::where('id',$loanId)->first();
        if(empty($record)){
            return $this->successResponse([],__('error.no_record_found'),200);
        }

        if(!$record->isPending()){
            return $this->errorResponse('Already approved or paid.',[],200);
        }

        $loanAmount = $record->loan_amount;
        $term = $record->loan_term;
        $emiAmount = $loanAmount/$term;

        $approvalDate = Carbon::now()->startOfDay();
        $emiDate='';
        
        for($i=1;$i<=$term;$i++){
            $emiDate = Carbon::parse($approvalDate)->addWeek($i)->format('Y-m-d');
            LoanPayment::create([
                'loan_id'=>$record->id,
                'user_id'=>$record->created_by,
                'emi_amount'=>$emiAmount,
                'emi_date'=>$emiDate,
            ]);
        }
        
        $record->status=1;
        $record->approval_date=Carbon::now()->format('Y-m-d');
        $record->update();

        return $this->successResponse($record,'Successfully Approved',201);
    }
}