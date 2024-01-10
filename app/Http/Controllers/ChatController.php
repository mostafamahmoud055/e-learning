<?php

namespace App\Http\Controllers;

use App\Events\ChatEvent;
use Illuminate\Http\Request;
use App\Traits\GeneralResponse;

class ChatController extends Controller
{
    use GeneralResponse;

    public function SendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required',
            // 'course_id' => 'required|exists:courses,id|integer',
        ]);
        broadcast(new ChatEvent($request->message))->toOthers();
        return $this->returnSuccessMessage("Message sent successfully");
    }
}
