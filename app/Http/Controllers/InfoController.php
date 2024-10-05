<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ImportInfo;
use App\Exports\ExportInfo;
use App\Models\Car;
use App\Models\User;
use App\Imports\CarDataImport;

class InfoController extends Controller
{
    public function importView(Request $request){
        return view('importFile');
    }
 
    public function exportInfos(Request $request){
        return Excel::download(new ExportInfo, 'Infos.xlsx');
    }
    public function showUploadForm()
    {
        return view('car_import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new CarDataImport, $request->file('file'));
            return redirect()->back()->with('success', 'Excel data imported successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing data: ' . $e->getMessage());
        }
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if ($row[0] == 'no') {
                // Skip header row
                continue;
            }

            $pin = $row[1];
            $purchasePrice = $row[2];
            $dubaiShipping = $row[3];
            $dubaiExp = $row[4];
            $erbilShipping = $row[5];
            $erbilExp = $row[6];
            $payPrice = $row[7];
            $userName = $row[8];

            // Find the user using LIKE for name
            $user = User::where('name', 'LIKE', '%' . $userName . '%')->first();

            // If user does not exist, create a new user
            if (!$user) {
                $user = User::create([
                    'name' => $userName,
                    // Add additional fields if necessary
                ]);
            }

            // Update the car based on the pin
            Car::updateOrCreate(
                ['pin' => $pin],
                [
                    'purchase_price' => $purchasePrice,
                    'dubai_shipping' => $dubaiShipping,
                    'dubai_exp' => $dubaiExp,
                    'erbil_shipping' => $erbilShipping,
                    'erbil_exp' => $erbilExp,
                    'pay_price' => $payPrice,
                    'user_id' => $user->id,
                ]
            );
        }
    }
}