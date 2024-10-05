<?php
namespace App\Imports;

use App\Models\Car;
use App\Models\User;
use App\Models\UserType;
use App\Models\Wallet;
use App\Models\Company;
use App\Models\Name;
use App\Models\CarModel;
use App\Models\Color;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class CarDataImport implements ToCollection
{
    public function __construct(){
        $this->url = env('FRONTEND_URL');
        $this->userClient =  UserType::where('name', 'client')->first()->id;

   }
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            if ($index == 0 || (!$row[1])) {
                // Skip the header row
                continue;
            }

            // Extract row values
            $no=$row[0];
            $pin = $row[1];
            $nameValue = $row[2];
            $companyValue = $row[3];
            $colorValue = $row[4];
            $modelValue = $row[5];
            $purchasePrice = $row[6];
            $dubaiShipping = $row[7];
            $dubaiExp = $row[8];
            $erbilShipping = $row[9];
            $erbilExp = $row[10];
            $payPrice = $row[12];
            $userName = $row[13];

            // Find or create the user by a LIKE query on the name
            $user = User::where('name', 'LIKE', '%' . $userName . '%')->first();

            if (!$user) {
                // If the user does not exist, create a new user
                $user = User::create([
                    'name' => $userName,
                    'type_id' => $this->userClient,
                    // Add other fields if needed
                ]);
                Wallet::create(['user_id' => $user->id]);

            }

            $company = Company::where('name', 'LIKE', '%' . $companyValue . '%')->first();
            if (!$company) {
                // If the company does not exist, create a new company
                $company = Company::create([
                    'name' => $companyValue,
                    // Add other fields if needed
                ]);
            }

            // Find or create the name by name
            $name = Name::where('name', 'LIKE', '%' . $nameValue . '%')->first();
            if (!$name) {
                // If the name does not exist, create a new name
                $name = Name::create([
                    'name' => $nameValue,
                    'company_id' => $company->id
                    // Add other fields if needed
                ]);
            }

            // Find or create the car model by name
            $carModel = CarModel::where('name', 'LIKE', '%' . $modelValue . '%')->first();
            if (!$carModel) {
                // If the car model does not exist, create a new car model
                $carModel = CarModel::create([
                    'name' => $modelValue,
                    // Add other fields if needed
                ]);
            }

            // Find or create the color by name
            $color = Color::where('name', 'LIKE', '%' . $colorValue . '%')->first();
            if (!$color) {
                // If the color does not exist, create a new color
                $color = Color::create([
                    'name' => $colorValue,
                    // Add other fields if needed
                ]);
            }


            // Update or create the car based on the pin
            Car::updateOrCreate(
                ['pin' => $pin],
                [
                    'no'=>$no,
                    'name_id' => $name->id,
                    'company_id' => $company->id,
                    'color_id' => $color->id,
                    'model_id' => $carModel->id,
                    'purchase_price' => $purchasePrice,
                    'paid_amount' => $purchasePrice,
                    'dubai_shipping' => $dubaiShipping,
                    'dubai_exp' => $dubaiExp,
                    'erbil_shipping' => $erbilShipping,
                    'erbil_exp' => $erbilExp,
                    'pay_price' => $payPrice,
                    'client_id' => $user->id==60 ? null: $user->id,
                    'user_id' => 60,
                    
                ]
            );
        }
    }
}
