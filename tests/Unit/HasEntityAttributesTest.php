<?php

namespace Tests\Unit;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Exceptions\InvalidAttributeException;
use App\Traits\HasEntityAttributes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Mockery;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class HasEntityAttributesTest extends TestCase
{
    use RefreshDatabase;

    protected $textAttribute;
    protected $numberAttribute;
    protected $dateAttribute;
    protected $selectAttribute;

    protected function setUp(): void
    {
        parent::setUp();

        // Create some attributes for testing
        $this->textAttribute = Attribute::create([
            'name' => 'Priority',
            'type' => Attribute::TYPE_TEXT,
        ]);

        $this->numberAttribute = Attribute::create([
            'name' => 'Budget',
            'type' => Attribute::TYPE_NUMBER,
        ]);

        $this->dateAttribute = Attribute::create([
            'name' => 'Due Date',
            'type' => Attribute::TYPE_DATE,
        ]);

        $this->selectAttribute = Attribute::create([
            'name' => 'Status',
            'type' => Attribute::TYPE_SELECT,
        ]);

        $this->selectAttribute->possibleValues()->createMany([
            ['key' => 'open', 'value' => 'Open'],
            ['key' => 'closed', 'value' => 'Closed'],
        ]);
    }

    /** @test */
    public function it_can_set_many_entity_attributes()
    {
        $entity = $this->getMockImplementationOfHasEntityAttributes();

        $attributes = [
            [
                'attribute_id' => $this->textAttribute->id,
                'value' => 'High',
            ],
            [
                'attribute_id' => $this->numberAttribute->id,
                'value' => 1000,
            ],
            [
                'attribute_id' => $this->dateAttribute->id,
                'value' => '2025-12-31',
            ],
            [
                'attribute_id' => $this->selectAttribute->id,
                'value' => 'open',
            ],
        ];

        $entity->setEntityAttributes($attributes);

        $this->assertDatabaseHas('attribute_values', [
            'entity_id' => $entity->id,
            'attribute_id' => $this->textAttribute->id,
            'value' => 'High',
        ]);

        $this->assertDatabaseHas('attribute_values', [
            'entity_id' => $entity->id,
            'attribute_id' => $this->numberAttribute->id,
            'value' => 1000,
        ]);

        $this->assertDatabaseHas('attribute_values', [
            'entity_id' => $entity->id,
            'attribute_id' => $this->dateAttribute->id,
            'value' => '2025-12-31',
        ]);

        $this->assertDatabaseHas('attribute_values', [
            'entity_id' => $entity->id,
            'attribute_id' => $this->selectAttribute->id,
            'value' => 'open',
        ]);
    }

    /** @test */
    public function it_fails_validation_for_invalid_attribute_values()
    {
        $entity = $this->getMockImplementationOfHasEntityAttributes();

        $invalidAttributes = [
            [
                'attribute_id' => $this->selectAttribute->id,
                'value' => 'invalid', // Invalid select value
            ],
            [
                'attribute_id' => $this->numberAttribute->id,
                'value' => 'not a number', // Invalid number value
            ],
            [
                'attribute_id' => $this->dateAttribute->id,
                'value' => 'invalid date', // Invalid date value
            ],
        ];

        $validator = $entity->createEntityAttributesValidator($invalidAttributes);

        $this->assertTrue($validator->fails());
        $errors = $validator->errors()->toArray();
        $this->assertArrayHasKey('attributes.0.value', $errors);
        $this->assertArrayHasKey('attributes.1.value', $errors);
        $this->assertArrayHasKey('attributes.2.value', $errors);
    }

    /** @test */
    public function it_updates_inserts_and_deletes_attributes()
    {
        $entity = $this->getMockImplementationOfHasEntityAttributes();

        // Initial attributes
        $initialAttributes = [
            [
                'attribute_id' => $this->textAttribute->id,
                'value' => 'Medium',
            ],
            [
                'attribute_id' => $this->numberAttribute->id,
                'value' => 500,
            ],
        ];

        $entity->setEntityAttributes($initialAttributes);

        // Updated attributes
        $updatedAttributes = [
            [
                'attribute_id' => $this->textAttribute->id,
                'value' => 'High', // Updated
            ],
            [
                'attribute_id' => $this->dateAttribute->id,
                'value' => '2025-12-31', // Inserted
            ],
        ];

        $entity->setEntityAttributes($updatedAttributes);

        $this->assertDatabaseHas('attribute_values', [
            'entity_id' => $entity->id,
            'attribute_id' => $this->textAttribute->id,
            'value' => 'High',
        ]);

        $this->assertDatabaseMissing('attribute_values', [
            'entity_id' => $entity->id,
            'attribute_id' => $this->numberAttribute->id,
            'value' => 1000,
        ]);

        $this->assertDatabaseHas('attribute_values', [
            'entity_id' => $entity->id,
            'attribute_id' => $this->dateAttribute->id,
            'value' => '2025-12-31',
        ]);
    }

    /** @test */
    public function it_throws_expected_exceptions_for_invalid_attributes()
    {
        $this->expectException(InvalidAttributeException::class);

        $entity = $this->getMockImplementationOfHasEntityAttributes();

        $invalidAttributes = [
            [
                'attribute_id' => -100, // Non-existent attribute ID
                'value' => 'High',
            ],
        ];

        $entity->setEntityAttributes($invalidAttributes);
    }

    /** @test */
    public function it_throws_validation_exception_for_invalid_request_attributes()
    {
        $this->expectException(ValidationException::class);

        $entity = $this->getMockImplementationOfHasEntityAttributes();

        $request = new \Illuminate\Http\Request([
            'attributes' => [
                [
                    'attribute_id' => -100, // Non-existent attribute ID
                    'value' => 'High',
                ],
            ],
        ]);

        $entity->setEntityAttributesFromRequest($request);
    }

    public function getMockImplementationOfHasEntityAttributes()
    {
        return new class extends Model {
            use HasEntityAttributes;
            
            protected $table = 'test_models';
            protected $guarded = [];

            public function __construct()
            {
                parent::__construct(['id' => 1]);
            }
        };
    }
}