<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Message::paginate(10);
        return view('message.index' , compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('message.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'message_key' => ['required'],
            'message_text' => ['required'],
        ]);

        if(Message::where('name' , $request['name'])->exists()){
            session()->flash('error' , 'این کلید معتبر نیست');
            return redirect()->back();
        }

        Message::create([
            'name' => $request['message_key'],
            'message' => $request['message_text'],
            'send_count' => 0,
            'created_at' => Carbon::now('asia/tehran'),
        ]);

        session()->flash('success' , 'پیام با موفقیت قبت شد');
        return redirect()->route('messages.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $message = Message::find($id);
        $message->delete();
        session()->flash('error' , 'حذف با موفقیت انجام شد');
        return redirect()->back();
    }
}
