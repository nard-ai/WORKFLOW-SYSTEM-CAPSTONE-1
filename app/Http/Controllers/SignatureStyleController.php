<?php

namespace App\Http\Controllers;

use App\Models\SignatureStyle;
use Illuminate\Http\JsonResponse;

class SignatureStyleController extends Controller
{
    public function index(): JsonResponse
    {
        $styles = SignatureStyle::all();
        return response()->json(['styles' => $styles]);
    }
}