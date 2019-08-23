<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       factory(\App\User::class)->create([
           'email'=>'admin@admin.com',
           'password'=>bcrypt('123123123')
       ]);
       factory(\App\User::class, 19)->create();
    }
}
