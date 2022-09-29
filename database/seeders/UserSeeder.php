<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        User::create([
            'name'=>'admin',
            'email'=>'admin@yopmail.com',
            'password'=>Hash::make('1234567890'),
            'user_type'=>1,
        ]);


        User::create([
            'name'=>'customer 1',
            'email'=>'customer1@yopmail.com',
            'password'=>Hash::make('1234567890'),
            'user_type'=>2,
        ]);
        User::create([
            'name'=>'customer 2',
            'email'=>'customer2@yopmail.com',
            'password'=>Hash::make('1234567890'),
            'user_type'=>2,
        ]);
    }
}
