<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;
use App\Models\User;
use App\Models\SystemConfig;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ImportInfo;
use App\Exports\ExportInfo;

class InfoController extends Controller
{
    public function __construct()
    {
        $this->url = env('FRONTEND_URL');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Info functionality removed - placeholder for future implementation
        return Inertia::render('Info/Index', ['url' => $this->url, 'message' => 'Info functionality temporarily removed']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Info functionality removed - placeholder for future implementation
        return Inertia::render('Info/Create', ['url' => $this->url, 'message' => 'Info functionality temporarily removed']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Info functionality removed - placeholder for future implementation
        return Response::json(['message' => 'Info functionality temporarily removed'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Info functionality removed - placeholder for future implementation
        return Response::json(['message' => 'Info functionality temporarily removed'], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Info functionality removed - placeholder for future implementation
        return Inertia::render('Info/Edit', ['url' => $this->url, 'id' => $id, 'message' => 'Info functionality temporarily removed']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Info functionality removed - placeholder for future implementation
        return Response::json(['message' => 'Info functionality temporarily removed'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Info functionality removed - placeholder for future implementation
        return Response::json(['message' => 'Info functionality temporarily removed'], 200);
    }

    /**
     * Show upload form for importing cars
     */
    public function showUploadForm()
    {
        // Info functionality removed - placeholder for future implementation
        return Inertia::render('Info/UploadForm', ['url' => $this->url, 'message' => 'Info functionality temporarily removed']);
    }

    /**
     * Import cars from Excel file
     */
    public function import(Request $request)
    {
        // Info functionality removed - placeholder for future implementation
        return Response::json(['message' => 'Info functionality temporarily removed'], 200);
    }

    /**
     * Export infos to Excel
     */
    public function exportInfos(Request $request)
    {
        // Info functionality removed - placeholder for future implementation
        return Response::json(['message' => 'Info functionality temporarily removed'], 200);
    }
}
