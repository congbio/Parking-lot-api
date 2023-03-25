<?php

namespace App\Http\Requests;

namespace App\Http\Requests;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ResetPassWordRequest extends FormRequest
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
            'email'=> 'required',
            'password' => 'required|min:8',
            'password_confirmation'=>'required|same:password',
        ];
    }
    public function messages(){
        return [
            'password.required' => 'Password is required!',
            'password.min' => "Password have to more than 8 characters",
            'password_confirmation.required' => 'The password confirmation field is required',
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
