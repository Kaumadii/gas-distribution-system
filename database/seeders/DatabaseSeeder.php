<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
            ]
        );

        $small = \App\Models\GasType::firstOrCreate(['name' => '2.8kg'], ['weight_kg' => 2.8, 'default_price' => 800]);
        $medium = \App\Models\GasType::firstOrCreate(['name' => '5kg'], ['weight_kg' => 5, 'default_price' => 1400]);
        $large = \App\Models\GasType::firstOrCreate(['name' => '12.5kg'], ['weight_kg' => 12.5, 'default_price' => 3200]);

        $supplier = \App\Models\Supplier::firstOrCreate(
            ['name' => 'Default Gas Supplier'],
            ['contact_person' => 'John', 'phone' => '0770000000']
        );

        foreach ([$small, $medium, $large] as $gasType) {
            \App\Models\SupplierRate::firstOrCreate(
                ['supplier_id' => $supplier->id, 'gas_type_id' => $gasType->id],
                ['rate' => $gasType->default_price * 0.85]
            );
        }

        $customer = \App\Models\Customer::firstOrCreate(
            ['name' => 'Demo Dealer', 'type' => 'dealer'],
            ['credit_limit' => 50000, 'outstanding_balance' => 0, 'phone' => '0712345678']
        );

        \App\Models\Stock::firstOrCreate(['gas_type_id' => $small->id], ['full_cylinders' => 50, 'empty_cylinders' => 10]);
        \App\Models\Stock::firstOrCreate(['gas_type_id' => $medium->id], ['full_cylinders' => 40, 'empty_cylinders' => 8]);
        \App\Models\Stock::firstOrCreate(['gas_type_id' => $large->id], ['full_cylinders' => 30, 'empty_cylinders' => 5]);
    }
}
