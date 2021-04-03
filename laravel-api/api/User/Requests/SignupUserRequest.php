Реєстрація користувача з екстеншенами чи екстеншеном
Апдейт екстеншена
Пассворд резет на користувача
Пассворд резет на extension

Додати тест, що юзеру пропису
<?php

namespace Api\User\Requests;

use Api\User\Models\User;
use Api\Domain\Models\Domain;
use Illuminate\Validation\Rule;
use Infrastructure\Http\ApiRequest;
use Infrastructure\Rules\UsernameRule;

class SignupUserRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'domain_name' => 'required|exists:' . Domain::class . ',domain_name',
            'user_email' => [
                'required',
                Rule::unique(User::class)->where(function ($query) {
                    $domain_name = $this->request->get('domain_name');
                    $domain = Domain::where('domain_name', $domain_name)->first();
                    if (empty($domain)) {
                        return false;
                    }
                    return $query->where('domain_uuid', $domain->domain_uuid);
                }),
            ],
            'username' => [
                'required',
                'string',
                'max:255',
                new UsernameRule(),
            ],
            'password' => 'required|min:6|max:25',
            'extensions' => 'required|array',
            'extensions.*.extension' => 'required|distinct|integer|min:1|max:999', // Переконатись, що естеншена нема ще
            'extensions.*.password' => 'required|min:6|max:25',
            'extensions.*.voicemail_password' => 'required|integer',




            // 'domain_name' => 'required|exists',
            // 'user.email' => 'required|email',
            // 'user.name' => 'required|string',
            // 'user.password' => 'required|string|min:8'
        ];
    }
}
