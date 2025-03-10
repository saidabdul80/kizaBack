<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Notification::filter($request->all())->latest()->paginate(15);
        return response()->json($notifications);
    }

    public function updateNotification(Request $request){
        $notification = Notification::find($request->id);
        $notification->update($request->all());
        return response()->json($notification);
    }
}
