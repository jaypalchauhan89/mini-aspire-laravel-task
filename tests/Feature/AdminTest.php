<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Loan;
use App\Models\LoanPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function accessable_by_admin_only()
    {
        $user = User::factory()->create();
        $this->actingAs($user)->json('GET', route('admin.loan'))
            ->assertStatus(401);
    }

    /** @test */
    public function loan_list_for_admin()
    {

        $admin = User::factory()->create(['user_type'=>1]);
        $user1 = User::factory()->create(['user_type'=>2]);
        $user2 = User::factory()->create(['user_type'=>2]);

        Loan::factory()->create(['loan_amount'=>9000,'loan_term'=>3,'created_by'=> $user1->id]);
        Loan::factory()->create(['loan_amount'=>6000,'loan_term'=>2,'created_by'=> $user1->id]);


        Loan::factory()->create(['loan_amount'=>12000,'loan_term'=>6,'created_by'=> $user2->id]);


        $this->actingAs($admin)->json('GET', route('admin.loan'))
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
    public function approve_loan()
    {

        $admin = User::factory()->create(['user_type'=>1]);
        $user1 = User::factory()->create(['user_type'=>2]);
        
        $loan = Loan::factory()->create(['loan_amount'=>9000,'loan_term'=>3,'created_by'=> $user1->id]);
        
        $this->actingAs($admin)->json('POST', route('admin.approveLoan',$loan->id))
            ->assertStatus(201);

    }
}