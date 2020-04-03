<?php


use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{

    public function run()
    {

        if (env('APP_ENV') != 'production') {
            $password = Hash::make('secret');

            for ($i = 1; $i <= 10; $i++) {
                $users[] = [
                    'email' => 'user' . $i . '@gitproject.com',
                    'username' => 'user' . $i,
                    'password' => $password
                ];
            }

            User::insert($users);
        }
    }
}