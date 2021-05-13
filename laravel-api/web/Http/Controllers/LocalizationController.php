<?php

namespace Web\Http\Controllers;

use Web\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;


class LocalizationController extends Controller
{
    public function lang($locale)
    {
        App::setLocale($locale);
        session()->put('locale', $locale);
        return redirect()->back();
    }
}
