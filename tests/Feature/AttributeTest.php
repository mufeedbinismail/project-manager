<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\AttributePossibleValue;
use App\Models\AttributeValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\AuthenticatesUser;

class AttributeTest extends TestCase
{
    use RefreshDatabase, AuthenticatesUser;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = false;

    /** @test */
    public function can_create_an_attribute_with_possible_values()
    {
        $attributeData = [
            'name' => 'priority',
            'type' => Attribute::TYPE_SELECT,
        ];

        $possibleValues = [
            ['key' => 'low', 'value' => 'Low'],
            ['key' => 'medium', 'value' => 'Medium'],
            ['key' => 'high', 'value' => 'High'],
        ];

        $response = $this->withAuthorizationHeader()
            ->post(
                route('attributes.store'),
                array_merge($attributeData, ['possibleValues' => $possibleValues])
            );

        $response->assertStatus(201);
        $this->assertDatabaseHas('attributes', $attributeData);
        foreach ($possibleValues as $possibleValue) {
            $this->assertDatabaseHas(
                'attribute_possible_values',
                array_merge($possibleValue, ['attribute_id' => $response->json('id')])
            );
        }

        $attributeData = [
            'name' => 'description',
            'type' => Attribute::TYPE_TEXT,
        ];

        $response = $this->withAuthorizationHeader()
            ->post(route('attributes.store'), $attributeData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('attributes', $attributeData);
    }

    /** @test */
    public function cannot_create_an_attribute_with_invalid_data()
    {
        $response = $this->withAuthorizationHeader()
            ->post(route('attributes.store'), [
                'name' => '',
                'type' => 'invalid_type',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'type']);
    }

    /** @test */
    public function can_update_an_attribute_and_its_possible_values()
    {
        $attribute = Attribute::create([
            'name' => 'priority',
            'type' => Attribute::TYPE_SELECT,
        ]);

        $possibleValues = [
            ['key' => 'low', 'value' => 'Low'],
            ['key' => 'medium', 'value' => 'Medium'],
            ['key' => 'high', 'value' => 'High'],
        ];

        $attribute->possibleValues()->createMany($possibleValues);

        $updatedAttributeData = [
            'name' => 'priority_updated',
            'type' => Attribute::TYPE_SELECT,
        ];

        $updatedPossibleValues = [
            ['key' => 'low', 'value' => 'Low Updated'],
            ['key' => 'medium', 'value' => 'Medium Updated'],
            ['key' => 'high', 'value' => 'High Updated'],
        ];

        $response = $this->withAuthorizationHeader()
            ->patch(
                route('attributes.update', $attribute->id),
                array_merge(
                    $updatedAttributeData,
                    ['possibleValues' => $updatedPossibleValues]
                )
            );

        $response->assertStatus(200);
        $this->assertDatabaseHas('attributes', $updatedAttributeData);
        foreach ($updatedPossibleValues as $updatedPossibleValue) {
            $this->assertDatabaseHas(
                'attribute_possible_values',
                array_merge($updatedPossibleValue, ['attribute_id' => $attribute->id])
            );
        }
    }

    /** @test */
    public function cannot_update_or_delete_possible_values_if_data_exists_for_that_specific_possible_value()
    {
        $attribute = Attribute::create([
            'name' => 'priority',
            'type' => Attribute::TYPE_SELECT,
        ]);

        $possibleValues = [
            ['key' => 'low', 'value' => 'Low'],
            ['key' => 'medium', 'value' => 'Medium'],
            ['key' => 'high', 'value' => 'High'],
        ];

        $possibleValues = $attribute->possibleValues()->createMany($possibleValues);

        // Create an attribute value that references one of the possible values
        AttributeValue::create([
            'attribute_id' => $attribute->id,
            'entity_id' => 1,
            'value' => 'low',
        ]);

        // Attempt to delete the possible value
        $response = $this->withAuthorizationHeader()
            ->patch(
                route('attributes.update', $attribute->id),
                array_merge(
                    $attribute->toArray(),
                    ['possibleValues' => $possibleValues->filter(fn($possibleValue) => $possibleValue->key != 'low')->toArray()]
                )
            );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['possibleValues']);

        // Attempt to update the possible value
        $response = $this->withAuthorizationHeader()
            ->patch(
                route('attributes.update', $attribute->id),
                array_merge(
                    $attribute->toArray(),
                    ['possibleValues' => $possibleValues->each(fn($possibleValue) => $possibleValue->key .= '_updated')->toArray()]
                )
            );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['possibleValues']);
    }

    /** @test */
    public function cannot_change_the_type_if_any_data_exists()
    {
        $attribute = Attribute::create([
            'name' => 'priority',
            'type' => Attribute::TYPE_SELECT,
        ]);

        // Create an attribute value that references the attribute
        AttributeValue::create([
            'attribute_id' => $attribute->id,
            'entity_id' => 1,
            'value' => 'low',
        ]);

        // Attempt to change the attribute type
        $response = $this->withAuthorizationHeader()
            ->patch(
                route('attributes.update', $attribute->id),
                array_merge($attribute->toArray(), ['type' => Attribute::TYPE_TEXT])
            );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['type']);
    }
}
