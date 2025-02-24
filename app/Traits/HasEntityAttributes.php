<?php

namespace App\Traits;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Exceptions\InvalidAttributeException;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

trait HasEntityAttributes
{
    /**
     * Get the attribute values associated with this entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function entityAttributes()
    {
        return $this->hasMany(AttributeValue::class, 'entity_id');
    }

    /**
     * Creates a validator for validating entity attributes.
     *
     * @param array $attributes
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function createEntityAttributesValidator(array $attributes, $key = 'attributes')
    {
        $attributes = [$key => $attributes];
        $validator = Validator::make($attributes, [
            "$key.*.attribute_id" => 'required|exists:attributes,id',
            "$key.*.value" => 'required',
        ]);

        $validator->after(function ($validator) use ($attributes, $key) {
            if ($validator->messages()->isNotEmpty()) {
                return;
            }

            $attributeInstances = Attribute::query()
                ->whereIn('id', array_column($attributes[$key], 'attribute_id'))
                ->get()
                ->keyBy('id');

            $rules = [];
            foreach ($attributes[$key] as $index => $attributeData) {
                $attribute = $attributeInstances->get($attributeData['attribute_id']);
                $rules["$key.$index.value"] = $attribute->getValidationRules();
            }

            $valueValidator = Validator::make($attributes, $rules);
            if ($valueValidator->fails()) {
                $validator->errors()->merge($valueValidator->errors());
            }
        });

        return $validator;
    }

    /**
     * Set the entity attributes.
     *
     * @param array $attributes
     * @throws \App\Exceptions\InvalidAttributeException
     */
    public function setEntityAttributes(array $attributes)
    {
        if (($validator = $this->createEntityAttributesValidator($attributes))->fails()) {
            throw new InvalidAttributeException($validator->errors());
        }

        $this->syncEntityAttributes($attributes);
    }

    /**
     * Set the entity attributes from the request.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $key
     * @throws \Illuminate\Validation\ValidationException
     */
    public function setEntityAttributesFromRequest($request, $key = 'attributes')
    {
        $attributes = $request->input($key, []);
        $validator = $this->createEntityAttributesValidator($attributes, $key);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        $this->syncEntityAttributes($attributes);
    }

    /**
     * Create or update the entity attributes.
     *
     * @param array $attributes
     */
    protected function syncEntityAttributes(array $attributes)
    {
        DB::transaction(function () use ($attributes) {
            foreach ($attributes as $attributeData) {
                $this->entityAttributes()->updateOrCreate(
                    ['attribute_id' => $attributeData['attribute_id']],
                    ['value' => $attributeData['value']]
                );
            }

            $this->entityAttributes()
                ->whereNotIn('attribute_id', array_column($attributes, 'attribute_id'))
                ->delete();

            DB::table('attribute_values as v')
                ->where('entity_id', $this->id)
                ->update([
                    'v.attribute_name' => DB::raw('(SELECT `name` FROM `attributes` WHERE `id` = `v`.`attribute_id`)'),
                    'v.attribute_type' => DB::raw('(SELECT `type` FROM `attributes` WHERE `id` = `v`.`attribute_id`)'),
                    'v.value_description' => DB::raw('(SELECT `value` FROM `attribute_possible_values` WHERE `key` = `v`.`value` AND `attribute_id` = `v`.`attribute_id`)'),
                ]);
        });
    }
}