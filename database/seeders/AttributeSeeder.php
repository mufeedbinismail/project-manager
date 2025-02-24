<?php

namespace Database\Seeders;

use App\Models\Attribute;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attributes = [
            ['name' => 'start_date', 'type' => Attribute::TYPE_DATE],
            ['name' => 'end_date', 'type' => Attribute::TYPE_DATE],
            ['name' => 'department', 'type' => Attribute::TYPE_TEXT],
            ['name' => 'priority', 'type' => Attribute::TYPE_SELECT, 'possibleValues' => [
                ['key' => 'low', 'value' => 'Low'],
                ['key' => 'medium', 'value' => 'Medium'],
                ['key' => 'high', 'value' => 'High'],
            ]],
            ['name' => 'status', 'type' => Attribute::TYPE_SELECT, 'possibleValues' => [
                ['key' => 'open', 'value' => 'Open'],
                ['key' => 'in_progress', 'value' => 'In Progress'],
                ['key' => 'closed', 'value' => 'Closed'],
            ]],
            ['name' => 'budget', 'type' => Attribute::TYPE_NUMBER],
        ];

        foreach ($attributes as $attributeData) {
            $possibleValues = $attributeData['possibleValues'] ?? null;
            unset($attributeData['possibleValues']);

            $attribute = Attribute::create($attributeData);

            if ($possibleValues) {
                $attribute->possibleValues()->createMany($possibleValues);
            }
        }
    }
}
