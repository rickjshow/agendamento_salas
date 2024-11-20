<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'nome' => 'Admin',
            'email' => 'admin@gmail.com',
            'senha' => Hash::make('senha123'), // Usando Hash para garantir a segurança da senha
            'papel' => 'admin',  // Atribuindo um papel, por exemplo, 'admin'
            'senha_resetada' => 'sim', // Ou 'sim', dependendo da lógica
            'status' => 'ativo'
        ]);
    }
}
