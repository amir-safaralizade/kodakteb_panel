<?php

namespace App\Http\Controllers;

use App\Models\SmsUser;
use App\Models\User;
use App\Models\Visit;
use App\services\PatientSearch;
use App\Http\Requests\PatientFormRequest;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = User::where('id', '>', 0)->orderby('id', 'desc')->paginate(30);
        return view('users.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {   
        $lastCaseNumber = (int) User::orderByRaw('CAST(caseNumber AS UNSIGNED) DESC')->value('caseNumber') + 1;
        while (User::where('caseNumber', $lastCaseNumber)->exists()) {
            $lastCaseNumber++;
        }
        
        return view('users.create' , compact('lastCaseNumber'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PatientFormRequest $request)
    {

        $user = new User();
        $user->caseNumber = $request['caseNumber'];
        $user->name = $request['name'];
        $user->lastName = $request['lastname'];
        $user->insurance_id = $request['insurance'];
        $user->nationalCode = $request['nationalCode'];
        $user->sex = $request['sex'];
        $user->birthday = $request['birthday'];
        $user->fatherName = $request['fatherName'];
        $user->motherName = $request['motherName'];
        $user->motherLastName = $request['motherLastName'];
        $user->phone = $request['phone'];
        $user->created_at = Carbon::now('asia/tehran');
        $user->save();

        $result = $this->sendCreateCaseSMS($request['caseNumber'], $user);
        session()->flash('success', 'ثبت نام کاربر با موفقیت انجام شد' . $result);
        if ($request->input('next') === 'visit') {
            return redirect()->route('visits.create', $user->id);
        }
        return redirect(route('user.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = User::findOrfail($id);
        return view('users.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $item = User::findOrfail($id);
        return view('users.edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PatientFormRequest $request, string $id)
    {

        $user = User::findOrFail($id);
        $user->caseNumber = $request['caseNumber'];
        $user->name = $request['name'];
        $user->lastName = $request['lastname'];
        $user->insurance_id = $request['insurance'];
        $user->nationalCode = $request['nationalCode'];
        $user->sex = $request['sex'];
        $user->birthday = $request['birthday'];
        $user->fatherName = $request['fatherName'];
        $user->motherName = $request['motherName'];
        $user->motherLastName = $request['motherLastName'];
        $user->phone = $request['phone'];
        $user->save();
        session()->flash('success', 'ویرایش کاربر با موفقیت انجام شد');
        return redirect(route('user.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Find the user by ID
        $user = User::find($id);

        if ($user) {
            // Delete the user
            $user->delete();

            // Delete associated visits
            Visit::where('user_id', $id)->delete();

            // Flash success message
            session()->flash('success', 'حذف با موفقیت انجام شد');
        } else {
            // User not found, flash error message
            session()->flash('error', 'کاربر مورد نظر پیدا نشد');
        }

        // Redirect back
        return redirect()->back();
    }

    public function searchSuggestions(Request $request, PatientSearch $search)
    {
        $request->validate(['q' => ['nullable', 'string', 'max:80']]);
        $term = $search->normalize((string) $request->input('q', ''));
        $items = mb_strlen($term) < 2 && !ctype_digit($term) ? collect() : $search->suggestions($term);

        return response()->json(['items' => $items->map(fn ($user) => [
            'name' => trim($user->name.' '.$user->lastName) ?: 'پرونده بدون نام',
            'case_number' => $user->caseNumber,
            'national_code' => $user->nationalCode,
            'profile_url' => route('user.show', $user->id),
            'visit_url' => route('visits.create', $user->id),
        ])])->header('Cache-Control', 'no-store, private');
    }

    public function searchUsers(Request $request, PatientSearch $search)
    {
        $request->validate(['searchInput' => ['nullable', 'string', 'max:80']]);
        $items = $search->query((string) $request->input('searchInput', ''))->paginate(30)
            ->appends($request->only('searchInput'));

        return view('users.index', compact('items'));
    }


    public function sendCreateCaseSMS($caseNumber, User $user)
    {
        if (!isset($user->phone) || empty($user->phone)) {
            $message = '<span style="color:red">';
            $message .= '(اطلاعات پرونده ناقص است.پیامکی ارسال نشد)';
            $message .= $message;
            return $message;
        }

        $params = [];
        $params[] = [
            'name' => 'CASENUMBER',
            'value' => $caseNumber,
        ];
        if (isset($user->name) && !empty($user->name)) {
            $templateId = 235702;
            $content = 'createCase';
            $params[] = [
                'name' => 'NAME',
                'value' => $user->name,
            ];
        }else{
            $templateId = 346055;
            $content = 'createCase_withoutname';
        }

        $API_KEY = config('properties.smsIrApiKey');
        $url = config('properties.smsIrVerifyUrl');
        $mobile = $user->phone;

        $header = [
            'Content-Type' => 'application/json',
            'Accept' => 'text/plain',
            'x-api-key' => $API_KEY,
        ];
        $data = [
            'mobile' => $mobile,
            'templateId' => $templateId,
            'Parameters' => $params,
        ];

        try {
            $response = Http::withHeaders($header)->post($url, $data);
        } catch (Exception $e) {
            return $message = "(وضعیت ارسال پیامک : خطا در ارتباط با سرور پیامک)";
        }

        if (isset($response) && !empty($response) && isset($response['status'])) {
            $status = $response['status'];
            if ($status == 1) {
                SmsUser::create([
                    'user_id' => $user->id,
                    'content' => $content,
                    'created_at' => Carbon::now('asia/tehran'),
                ]);
                return $message = '(پیامک ایجاد پرونده با موفقیت ارسال شد)';
            } else {
                $message = '<span style="color:red">';
                $message .= '(';
                $message .= 'وضعیت ارسال پیامک : ' . $response['message'];
                $message .= ')';
                $message .= '</span>';
                return $message;
            }
        } else {
            $message = '<span style="color:red">';
            $message .= "(وضعیت ارسال پیامک : خطا در ارتباط با سرور پیامک)";
            $message .= '</span>';
            return $message;
        }

    }
}
