<?php

namespace Database\Seeders;

use App\Models\Sekolah;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SekolahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kepalaSekolah = User::query()->firstOrCreate(
            [
                'username' => 'kepsek1',
            ],
            [
                'name' => 'Feny Irfany Muhammad,ST.,M.AP.',
                'password' => Hash::make('password123')
            ]
        );
        $kepalaSekolah->assignRole('Kepala Sekolah');
        $staffKepalaSekolah = Staff::query()->firstOrCreate(
            [
                'user_id' => $kepalaSekolah->id
            ],
            [
                'nuptk' => $kepalaSekolah->username,
                'nama' => $kepalaSekolah->name,
                'jenis_kelamin' => 'l',
            ]
        );

        Sekolah::query()->firstOrCreate(
            ['nama_sekolah' => 'SMK IT BAITUL AZIZ'],
            [
                'nama_sekolah' => 'SMK IT BAITUL AZIZ',
                'npsn' => '69908714',
                'alamat' => 'Jl. Pesantren Baitul Aziz - Kp. Sukahaji No.44',
                'kode_pos' => '40382',
                'telp' => '022-5950175',
                'kelurahan_id' => '32.04.33.2010',
                'website' => 'www.smkitbaitulaziz.sch.id',
                'email' => 'smkitbaitulaziz@gmail.com',
                'staff_id' => $staffKepalaSekolah->staff_id,
            ]
        );
    }
}
