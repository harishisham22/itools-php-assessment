<?php

namespace App\Http\Requests;

use App\BuildingType;
use App\Models\Tariff;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateTariffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'building_type' => ['required', Rule::enum(BuildingType::class)],
            'category' => ['required', 'string'],
            'min_kwh' => ['required', 'integer', 'min:0'],
            'max_kwh' => ['nullable', 'integer', 'gt:min_kwh'],
            'rate' => ['required', 'numeric', 'min:0'],
            'is_initial_rate' => ['required', 'boolean'],
        ];
    }

    /**
     * Get the "after" validation hooks for the request.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->any()) {
                    return;
                }

                $tariffModel = new Tariff();

                $isValid = $tariffModel->validateNoOverlap(
                    $this->input('building_type'),
                    $this->input('min_kwh'),
                    $this->input('max_kwh')
                );

                if (!$isValid) {
                    $validator->errors()->add(
                        'min_kwh',
                        'The specified kWh range overlaps with an existing tariff tier for this building type.'
                    );
                }
            }
        ];
    }
}
