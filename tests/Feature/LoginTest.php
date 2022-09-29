<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function send_token_on_correct_credentials()
    {
        $user = User::factory()->create();

        $this->json('POST', route('loginUser'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertStatus(200)->assertJson(function (AssertableJson $json) use ($user) {
            $json->has('data.0.token')
                ->etc();
        });
    }

    
    /** @test */
    public function it_validates_wrong_password()
    {
        $user = User::factory()->create();

        $this->json('POST', route('loginUser'), [
            'email' => $user->email,
            'password' => 'random',
        ])->assertStatus(403)->assertJson(function (AssertableJson $json) use ($user) {
            $json
                ->where('message', __('auth.password'))
                ->etc();
        });
    }
}