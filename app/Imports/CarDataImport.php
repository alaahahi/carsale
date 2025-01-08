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
            $name = $row[10] . ' '. $row[2];
            $color = $row[3];
            $model = $row[4] ;
            $purchasePrice = $row[5];
            $dubaiShipping = $row[6];
            $dubaiExp = $row[7];
            $erbilShipping = $row[8];
            $erbilExp = $row[9];
            $source=$row[11] ? $row[11] : 'Nejoum';


            // Update or create the car based on the pin
            Car::updateOrCreate(
                ['pin' => $pin],
                [
                    'no'=>$no,
                    'name'=>$name,
                    'model'=>$model,
                    'color'=>$color,
                    'source'=>$source,
                    'purchase_price' => $purchasePrice,
                    'dubai_shipping' => $dubaiShipping,
                    'dubai_exp' => $dubaiExp,
                    'erbil_shipping' => $erbilShipping,
                    'erbil_exp' => $erbilExp,
                    'user_id' => 60,
                    
                ]
            );
        }
    }
}
