<?php

namespace App\Http\Controllers;

use App\Models\CustomNotification;
use Illuminate\Http\Request;

class CustomNotificationController extends Controller
{
    public function show(CustomNotification $notification)
    {
        return $notification;
    }
}
