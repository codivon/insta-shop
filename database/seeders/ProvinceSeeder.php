<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $provinces = [
            [
                'name' => 'اردبيل',
                'latitude' => '38.65',
                'longitude' => '48.12',
            ],
            [
                'name' => 'اصفهان',
                'latitude' => '32.57',
                'longitude' => '52.67',
            ],
            [
                'name' => 'البرز',
                'latitude' => '36.02',
                'longitude' => '50.87',
            ],
            [
                'name' => 'ايلام',
                'latitude' => '32.99',
                'longitude' => '46.92',
            ],
            [
                'name' => 'آذربايجان شرقي',
                'latitude' => '38.32',
                'longitude' => '46.67',
            ],
            [
                'name' => 'آذربايجان غربي',
                'latitude' => '38.07',
                'longitude' => '45.93',
            ],
            [
                'name' => 'بوشهر',
                'latitude' => '28.51',
                'longitude' => '51.39',
            ],
            [
                'name' => 'تهران',
                'latitude' => '35.55',
                'longitude' => '51.79',
            ],
            [
                'name' => 'چهارمحال وبختياري',
                'latitude' => '31.89',
                'longitude' => '50.43',
            ],
            [
                'name' => 'خراسان جنوبي',
                'latitude' => '32.81',
                'longitude' => '58.33',
            ],
            [
                'name' => 'خراسان رضوي',
                'latitude' => '35.91',
                'longitude' => '58.96',
            ],
            [
                'name' => 'خراسان شمالي',
                'latitude' => '37.57',
                'longitude' => '57.37',
            ],
            [
                'name' => 'خوزستان',
                'latitude' => '31.27',
                'longitude' => '49.07',
            ],
            [
                'name' => 'زنجان',
                'latitude' => '36.58',
                'longitude' => '48.31',
            ],
            [
                'name' => 'سمنان',
                'latitude' => '35.89',
                'longitude' => '54.58',
            ],
            [
                'name' => 'سيستان وبلوچستان',
                'latitude' => '28.21',
                'longitude' => '61.42',
            ],
            [
                'name' => 'فارس',
                'latitude' => '29.13',
                'longitude' => '53.22',
            ],
            [
                'name' => 'قزوين',
                'latitude' => '36.22',
                'longitude' => '49.79',
            ],
            [
                'name' => 'قم',
                'latitude' => '34.71',
                'longitude' => '51.07',
            ],
            [
                'name' => 'كردستان',
                'latitude' => '35.73',
                'longitude' => '46.89',
            ],
            [
                'name' => 'كرمان',
                'latitude' => '29.09',
                'longitude' => '57.11',
            ],
            [
                'name' => 'كرمانشاه',
                'latitude' => '34.52',
                'longitude' => '46.79',
            ],
            [
                'name' => 'كهگيلويه وبويراحمد',
                'latitude' => '30.48',
                'longitude' => '50.84',
            ],
            [
                'name' => 'گلستان',
                'latitude' => '37.45',
                'longitude' => '55.21',
            ],
            [
                'name' => 'گيلان',
                'latitude' => '37.51',
                'longitude' => '49.59',
            ],
            [
                'name' => 'لرستان',
                'latitude' => '33.52',
                'longitude' => '48.41',
            ],
            [
                'name' => 'مازندران',
                'latitude' => '36.45',
                'longitude' => '52.32',
            ],
            [
                'name' => 'مركزي',
                'latitude' => '34.46',
                'longitude' => '49.99',
            ],
            [
                'name' => 'هرمزگان',
                'latitude' => '26.99',
                'longitude' => '56.42',
            ],
            [
                'name' => 'همدان',
                'latitude' => '34.91',
                'longitude' => '48.64',
            ],
            [
                'name' => 'يزد',
                'latitude' => '31.48',
                'longitude' => '54.86',
            ],
        ];

        $ordering = 1;

        foreach ($provinces as $province) {
            Province::firstOrCreate(
                [
                    'name'      => $province['name']
                ],
                [
                    'latitude'  => $province['latitude'],
                    'longitude' => $province['longitude'],
                    'ordering'  => $ordering++,
                ]
            );
        }
    }
}
