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
            $name = $row[2] +' '+$row[10];
            $color = $row[3];
            $model = $row[4] ;
            $purchasePrice = $row[5];
            $dubaiShipping = $row[6];
            $dubaiExp = $row[7];
            $erbilShipping = $row[8];
            $erbilExp = $row[9];



            // Update the car based on the pin
            Car::updateOrCreate(
                ['pin' => $pin],
                [
                    'name'=>$name,
                    'model'=>$model,
                    'color'=>$color,
                    'source'=>$source,
                    'purchase_price' => $purchasePrice,
                    'dubai_shipping' => $dubaiShipping,
                    'dubai_exp' => $dubaiExp,
                    'erbil_shipping' => $erbilShipping,
                    'erbil_exp' => $erbilExp,

                ]
            );
        }
    }
}