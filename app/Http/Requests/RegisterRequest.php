<?php

namespace App\Http\Requests;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class RegisterRequest extends FormRequest
{
    use ApiResponse;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            "fullName" => 'required|string|min:3|max:50|alpha',
            'email' => 'required|max:255|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'regex:/^(?=.*[@#])[a-zA-Z0-9\s@#]+$/'
            ],
            'password_confirmation'=>'required|same:password',

        ];
            }
    public function messages()
    {
        return [
            'fullName.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.unique' => 'Email is duplicate',
            'email.email'=> "Invalid format Email",
            'email.max' => "The length have to less 255 characters",
            'password.required' => 'Password Required',
            'password.min' => "Password have to more than 8 characters",
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        throw new HttpResponseException($this->responseErrorWithDetails(
            "exception.common.data.validate",
            $errors,
            Response::HTTP_UNPROCESSABLE_ENTITY
        ));
    }

}
