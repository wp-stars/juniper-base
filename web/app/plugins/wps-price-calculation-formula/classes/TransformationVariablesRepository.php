<?php

namespace PriceCalculationFormula;

class TransformationVariablesRepository
{

    /**
     * @var array|int[]
     */
    public array $variables = [
        'transformation_rh' => 0,
        'transformation_pt' => 0,
        'transformation_ru' => 0,
        'transformation_au' => 0,
        'transformation_ag' => 0,
        'transformation_pd' => 0,
        'transformation_rh_schwarz' => 0,
        'price_rhodium' => 0,
        'price_platin' => 0,
        'price_ruthenium' => 0,
        'price_gold' => 0,
        'price_silber' => 0,
        'price_palladium' => 0
    ];

    public function get(): array
    {
        return $this->variables;
    }

    public function __construct()
    {
        add_action('acf/init', [$this, 'init']);
    }

    public function init(): void
    {
        foreach ($this->variables as $key => $value) {
            $this->getSingleValue($key);
        }
    }

    public function getSingleValue(string $slug): float
    {

        if(!function_exists('get_field')){
            throw new \Exception('ACF is not installed');
        }

        $this->variables[$slug] = (float) get_field($slug, 'option');

        return $this->variables[$slug];
    }

}