<?php

namespace App\Support;

class ProductForm
{
    /**
        Return field definitions used by create/edit blades.
     */
    public static function fields(string $type = 'physical'): array
    {
        return [
            ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
            ['key' => 'description', 'label' => 'Description', 'type' => 'textarea'],
            ['key' => 'image', 'label' => 'Image', 'type' => 'file'],
            [
                'key' => 'type',
                'label' => 'Product Type',
                'type' => 'select',
                'options' => ['physical' => 'Physical', 'digital' => 'Digital'],
                'default' => $type ?: 'physical',
                'required' => true,
            ],
            ['key' => 'price', 'label' => 'Price', 'type' => 'number', 'step' => '0.01', 'required' => true],
            ['key' => 'discount_price', 'label' => 'Discount Price', 'type' => 'number', 'step' => '0.01'],
            [
                'key' => 'category',
                'label' => 'Category',
                'type' => 'select',
                'options' => [
                    'standard' => 'Standard',
                    'vendors' => 'Vendors',
                    'digital_tools' => 'Digital Tools',
                ],
                'default' => 'standard',
            ],
            ['key' => 'stock', 'label' => 'Stock', 'type' => 'number', 'default' => 0],
            ['key' => 'target_audience', 'label' => 'Target Audience', 'type' => 'text'],
            ['key' => 'delivery_type', 'label' => 'Delivery Type', 'type' => 'text'],
            [
                'key' => 'status',
                'label' => 'Status',
                'type' => 'select',
                'options' => ['active' => 'Active', 'inactive' => 'Inactive'],
                'default' => 'active',
            ],
        ];
    }

    /**
        Build validation rules based on fields.
     */
    public static function rules(array $fields, ?int $ignoreId = null): array
    {
        $rules = [];

        foreach ($fields as $field) {
            $key = $field['key'];
            $rule = [];

            $rule[] = !empty($field['required']) ? 'required' : 'nullable';

            $typeRule = match ($field['type']) {
                'number' => 'numeric',
                'file' => 'image|max:2048',
                default => 'string',
            };
            $rule[] = $typeRule;

            if (($field['type'] ?? null) === 'select' && !empty($field['options'])) {
                $rule[] = 'in:' . implode(',', array_keys($field['options']));
            }

            $rules[$key] = implode('|', $rule);
        }

        $rules['display_id'] = 'nullable|string|unique:products,display_id' . ($ignoreId ? ',' . $ignoreId : '');
        $rules['sku'] = 'nullable|string|unique:products,sku' . ($ignoreId ? ',' . $ignoreId : '');

        return $rules;
    }
}
