<?php

namespace WPS\PriceCalculationFormula;

use \WC_Product_Simple;
use \WC_Product;

class FormulaProduct
{

    public int $id;
    public bool $activeRule = false;
    private string $formula = "";
    private TransformationVariablesRepository|null $transformationVariablesRepository = null;
    public WC_Product_Simple|WC_Product $product;

    public function __construct(int $id, TransformationVariablesRepository|null $transformationVariablesRepository = null){
        $this->id = $id;
        $this->product = wc_get_product($this->id);
        $this->updateStatus();
        $this->updateFormula();
        $this->transformationVariablesRepository = $transformationVariablesRepository;

        if($this->transformationVariablesRepository === null){
            $this->transformationVariablesRepository = new TransformationVariablesRepository();
        }

        add_action('init', [$this, 'init']);
    }

    public function init(){
        $this->transformationVariablesRepository->init();
    }

    private function updateStatus(): void
    {
        $this->activeRule = get_field('iwg_price_formular_active', $this->id);
    }

    private function updateFormula(): void
    {
        $this->formula = get_field('iwg_price_formular', $this->id);
    }

    public function updatePrice(): bool
    {

        if(!$this->activeRule){
           return false;
        }

        $newPrice = $this->formulaInterpreter($this->transformationVariablesRepository);
        $this->product->set_price($newPrice);
        $this->product->set_regular_price($newPrice);
        $result = $this->product->save();

        if(is_int($result) && $result > 0){
            return true;
        }

        return false;
    }

    public function formulaInterpreter(TransformationVariablesRepository $transformationVariablesRepository): float
    {
        // replace every single value inside the formula
        foreach ($transformationVariablesRepository->get() as $key => $value) {
            $this->formula = $this->replaceSingleVariable($this->formula, $key, $value);
        }

        // Evaluate the expression and return the result
        $result = eval("return {$this->formula};");

        try {
            if ($result === false) {
                throw new \Exception('The formula could not be evaluated');
            }
        } catch (\Exception $e) {
            return 0.0;
        }

        return (float) $result * 1.0;
    }

    public function replaceSingleVariable($input, $variable, $replacement){
        return preg_replace('/@@' . $variable . '@@/', $replacement, $input);
    }
}