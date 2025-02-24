<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class AttributeController
 *
 * Handles the creation and updating of attributes.
 *
 * @package App\Http\Controllers
 */
class AttributeController extends Controller
{
    /**
     * Store a newly created attribute in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:'.implode(',', Attribute::possibleTypes()),
            'possibleValues' => 'required_if:type,'.Attribute::TYPE_SELECT.'|array',
            'possibleValues.*.key' => 'required_with:possibleValues|string|max:255',
            'possibleValues.*.value' => 'required_with:possibleValues|string|max:255',
        ]);

        $attribute = Attribute::create($validatedData);

        if ($request->type === Attribute::TYPE_SELECT && $request->has('possibleValues')) {
            foreach ($request->possibleValues as $possibleValue) {
                $attribute->possibleValues()->create($possibleValue);
            }
        }

        return response()->json($attribute, 201);
    }

    /**
     * Update the specified attribute in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Attribute  $attribute
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Attribute $attribute)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:'.implode(',', Attribute::possibleTypes()),
            'possibleValues' => 'required_if:type,'.Attribute::TYPE_SELECT.'|array',
            'possibleValues.*.id' => 'sometimes|exists:attribute_possible_values,id',
            'possibleValues.*.key' => 'required_with:possibleValues|string|max:255',
            'possibleValues.*.value' => 'required_with:possibleValues|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validatedData = $validator->validated();

        if ($attribute->values()->exists()) {
            if ($attribute->type != $request->type) {
                $validator->errors()->add('type', 'Cannot change attribute type as it already has data.');
                throw new ValidationException($validator);
            }

            if ($attribute->type == Attribute::TYPE_SELECT) {
                $updatesToPossibleValues = collect($request->possibleValues)->keyBy('key');
                foreach ($attribute->possibleValues as $possibleValue) {
                    $dataExists = $attribute->values()->where('value', $possibleValue->key)->exists();
                    $possibleValueGotDeleted = empty($_possibleValue = $updatesToPossibleValues->get($possibleValue->key));
                    $possibleValueGotChanged = ($_possibleValue['id'] ?? null) != $possibleValue->id;

                    if ($dataExists && ($possibleValueGotDeleted || $possibleValueGotChanged)) {
                        $validator->errors()->add('possibleValues', sprintf('Cannot change the key %s of the possible value as it already has data.', $possibleValue->key));
                        throw new ValidationException($validator);
                    }
                }
            }
        }

        DB::transaction(function () use ($request, $validatedData, $attribute) {
            $attribute->update($validatedData);

            // Cache the attribute name so we can avoid un-necessarily complex query,
            // storage is cheap now a days
            AttributeValue::where('attribute_id', $attribute->id)
                ->update(['attribute_name' => $attribute->name]);

            if ($attribute->type === Attribute::TYPE_SELECT && $request->has('possibleValues')) {
                $idsNotToDelete = collect($request->possibleValues)->pluck('id')->filter()->unique()->toArray();
    
                // Delete possible values that are not present in the request
                $attribute->possibleValues()->whereNotIn('id', $idsNotToDelete)->delete();
    
                foreach ($request->possibleValues as $possibleValue) {
                    $attribute->possibleValues()->updateOrCreate(
                        ['id' => $possibleValue['id'] ?? null],
                        $possibleValue
                    );

                    // We don't normally update the attributes once defined, so
                    // This many queries are acceptable once in a while
                    AttributeValue::where('attribute_id', $attribute->id)
                        ->where('value', $possibleValue['key'])
                        ->update([
                            'value_description' => $possibleValue['value']
                        ]);
                }
            }
        });

        return response()->json($attribute, 200);
    }
}
