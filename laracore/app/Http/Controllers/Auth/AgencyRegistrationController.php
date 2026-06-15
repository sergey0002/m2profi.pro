<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AgencyRegistrationController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register-agency');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'caption' => 'required|string|max:255',
            'inn' => 'required|string|max:12', // ИНН может быть 10 или 12 цифр
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'admin_phone' => 'required|string|max:20',
            'organization_card' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:5120',
            'email_form' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:5120',
        ]);

        // Проверка на занятый email или телефон
        $existingUser = User::where('e_mail', $request->admin_email)
            ->orWhere('phone', $request->admin_phone)
            ->first();

        if ($existingUser) {
            return back()->withInput()->withErrors([
                'admin_email' => 'Пользователь с таким email или телефоном уже зарегистрирован. Вы можете <a href="' . route('password.request') . '">восстановить пароль</a>.',
            ]);
        }

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Сохранение заявки
        try {
            DB::beginTransaction();

            // Загрузка файлов
            $orgCardPath = $request->file('organization_card')->store('agency_applications', 'public');
            $emailFormPath = $request->file('email_form')->store('agency_applications', 'public');

            $registrationData = [
                'admin_name' => $request->admin_name,
                'admin_email' => $request->admin_email,
                'admin_phone' => $request->admin_phone,
                'org_card' => $orgCardPath,
                'email_form' => $emailFormPath,
            ];

            Agency::create([
                'caption' => $request->caption,
                'inn' => $request->inn,
                'registration_status' => Agency::STATUS_APPLICATION,
                'registration_data' => $registrationData,
                'unactiv' => 1, // Изначально заблокировано
                'add_datetime' => now(),
            ]);

            DB::commit();

            return redirect()->route('register-agency')->with('success', 'Ваша заявка успешно отправлена и будет рассмотрена администратором в ближайшее время.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Произошла ошибка при сохранении заявки: ' . $e->getMessage()])->withInput();
        }
    }
}
