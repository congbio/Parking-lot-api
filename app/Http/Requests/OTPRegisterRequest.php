<?php

namespace App\Http\Requests;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class OTPRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    use ApiResponse;
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
            'otp' => 'required|integer',
            'email'=>'required',
        ];
    }
    public function messages(){
        return[
            'otp.required' => 'OTP is required',
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
