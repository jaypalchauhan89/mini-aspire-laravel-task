<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Loan;
use App\Models\LoanPayment;
use Carbon\Carbon;

class LoanTest extends TestCase
{
    use RefreshDatabase;
    
    public function apply_loan()
    {

        $user = User::factory()->create(['user_type'=>2]);

        $loanData = [
                'title'=>'Loan 1',
                'loan_amount'=>5000,
                'loan_term'=>3,
                'application_date'=>Carbon::now(),
                'created_by'=> $user->id
            ];
        
        $this->actingAs($user)->json('POST', route('applyLoan'), $loanData)
            ->assertStatus(201);

        $this->assertDatabaseHas('loans', [
            'loan_title' => $loanData['title'],
            'loan_amount'=>$loanData['loan_amount'],
            'loan_term'=>$loanData['loan_term'],
        ]);    
    }


    /** @test */
    public function my_loans()
    {

        $user = User::factory()->create(['user_type'=>2]);

        Loan::factory()->create(['loan_amount'=>6000,'loan_term'=>3,'created_by'=> $user->id]);
        Loan::factory()->create(['loan_amount'=>12000,'loan_term'=>3,'created_by'=> $user->id]);

        $this->actingAs($user)->json('GET', route('loan'))
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('data.0', function ($json) {
                        $json
                            ->has('loan_title')
                            ->etc();
                    })
                    ->has('data.1', function ($json) {
                        $json
                            ->has('loan_title')
                            ->etc();
                    })
                    ->etc();
            })
            ->assertStatus(200);    
    }



    /** @test */
    public function repayment_against_scheduled_payment()
    {

        $user = User::factory()->create(['user_type'=>2]);
        $amount = 6000;
        $term = 2;
        $loan = Loan::factory()->create(['loan_title'=>'Loan 1','loan_amount'=>$amount,'loan_term'=>$term,'created_by'=> $user->id,'status'=>1]);
        $approvalDate = Carbon::now();
        $emiDate = Carbon::parse($approvalDate)->addWeek(1)->format('Y-m-d');
        $paymentAmount = $amount/$term;
        $loanPayment1 = LoanPayment::factory()->create([
            'loan_id'=>$loan->id,
            'user_id'=>$user->id,
            'emi_amount'=>$paymentAmount,
            'emi_date'=>$emiDate,
            'status'=>0,
        ]);
        $emiDate = Carbon::parse($approvalDate)->addWeek(2)->format('Y-m-d');
        $loanPayment2 = LoanPayment::factory()->create([
            'loan_id'=>$loan->id,
            'user_id'=>$user->id,
            'emi_amount'=>$paymentAmount,
            'emi_date'=>$emiDate,
            'status'=>0,
        ]);

        $input = [
            'amount'=>3000,
        ];
        $this->actingAs($user)->json('POST', route('repaymentAgainstEMI',[$loan->id,$loanPayment1->id]),$input)->assertStatus(201);
    }



    /** @test */
    public function repayment_against_scheduled_payment_with_amount_same_or_greator_validation()
    {

        $user = User::factory()->create(['user_type'=>2]);
        $amount = 6000;
        $term = 2;
        $loan = Loan::factory()->create(['loan_title'=>'Loan 1','loan_amount'=>$amount,'loan_term'=>$term,'created_by'=> $user->id,'status'=>1]);
        $approvalDate = Carbon::now();
        $emiDate = Carbon::parse($approvalDate)->addWeek(1)->format('Y-m-d');
        $paymentAmount = $amount/$term;
        $loanPayment1 = LoanPayment::factory()->create([
            'loan_id'=>$loan->id,
            'user_id'=>$user->id,
            'emi_amount'=>$paymentAmount,
            'emi_date'=>$emiDate,
            'status'=>0,
        ]);
        $emiDate = Carbon::parse($approvalDate)->addWeek(2)->format('Y-m-d');
        $loanPayment2 = LoanPayment::factory()->create([
            'loan_id'=>$loan->id,
            'user_id'=>$user->id,
            'emi_amount'=>$paymentAmount,
            'emi_date'=>$emiDate,
            'status'=>0,
        ]);

        $input = [
            'amount'=>1000,
        ];
        $this->actingAs($user)->json('POST', route('repaymentAgainstEMI',[$loan->id,$loanPayment1->id]),$input)->assertStatus(422)->assertJson(function (AssertableJson $json) use ($user) {
            $json
                ->where('message', __('error.payment_amount_must_be_greator_or_same'))
                ->etc();
        });
    }

}
