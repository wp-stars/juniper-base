<?php

namespace PriceCalculationFormula;

class Plugin
{

    private string $pluginDir;
    private string $fieldgroupDir;
    private string $name = 'WPS Price Calculation Formula';
    private string $optionsPageName = 'IWG Verwaltung';
    private string $optionsPageSlug = 'wps-price-calculation-formula-settings';
    private array $activePlugins = [];

    public function __construct(string $pluginDir)
    {
        // setup instance variables
        $this->pluginDir = $pluginDir;
        $this->fieldgroupDir = $pluginDir . '/fieldgroups/';

        // run wordpress hooks
        add_action('plugins_loaded', [$this, 'pluginsLoaded']);
        add_action('init', [$this, 'init']);
        add_action('acf/render_field/name=iwg_price_formular', [$this, 'descriptionsForFields']);
        //add_action('add_meta_boxes', [$this, 'metaboxes']);

        new PriceFormulaHandler();
    }

    public function init()
    {
        // check if all plugins are installed and active
        add_action('admin_notices', [$this, 'adminNotices']);

        // setup all the ACF options page and fields
        $this->installOptionsPage();
        $this->installProductFields();
    }

    public function pluginsLoaded()
    {
       $this->activePlugins = apply_filters('active_plugins', get_option('active_plugins'));
    }

    public function metaboxes(){
        add_meta_box(
            'wps_price_calculation_formula',
            'Logging',
            [$this, 'renderMetabox'],
            'product',
            'side',
            'high'
        );
    }

    public function renderMetabox($post){

        // show the last 5 price updates
        $latestProductUpdates = Logger::getLatestProductUpdates($post->ID);

        if(!!$latestProductUpdates && is_array($latestProductUpdates) && count($latestProductUpdates)>0){
            foreach ($latestProductUpdates as $productUpdate){
                $date = new \DateTime($productUpdate->date);
                $user = get_user_by('id', $productUpdate->user_id);

                if($user){
                    echo "<p>{$date->format('d.m.Y H:i:s')}<br>{$user->user_login} - {$productUpdate->new_price}€</p>";
                }else{
                    echo "<p>{$date->format('d.m.Y H:i:s')}<br>System - {$productUpdate->new_price}€</p>";
                }
            }
        }
    }

    public function descriptionsForFields(){

        $variables = new TransformationVariablesRepository();
        $variables->init();
        $variables = $variables->get();

        echo '<div style="display: flex;flex-direction: row; flex-wrap: wrap; gap: 20px;margin-top: 15px;">';
        foreach ($variables as $key => $value) {
            echo "<div style='border-bottom: solid 1px #ccc; padding-bottom: 5px; width: 33%;display: flex;flex-direction: row;justify-content: space-between;'>
                    <strong>@@{$key}@@</strong>
                    <span>{$value}</span>
                </div>";
        }
        echo '</div>';
    }

    public function installProductFields(): void
    {
        if (function_exists('acf_add_local_field_group')){
            acf_add_local_field_group([
                'key' => 'group_65e869a87e0e5',
                'title' => 'IWG Preis Formel',
                'fields' => include $this->fieldgroupDir . 'product-group.php',
                'location' => [[[
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'product'
                ]]]
            ]);
        }
    }

    public function installOptionsPage(): void
    {
        if( function_exists('acf_add_options_page') ) {
            acf_add_options_page(array(
                'page_title' 	=> $this->optionsPageName,
                'menu_title'	=> $this->optionsPageName,
                'menu_slug' 	=> $this->optionsPageSlug,
                'capability'	=> 'edit_posts',
                'redirect'		=> false,
                'icon_url'      => 'dashicons-money'
            ));

            if (function_exists('acf_add_local_field_group')){
                acf_add_local_field_group([
                    'key' => 'group_65e855f5bafa3',
                    'title' => 'IWG Verwaltung',
                    'fields' => include $this->fieldgroupDir . 'options-group.php',
                    'location' => [[[
                        'param' => 'options_page',
                        'operator' => '==',
                        'value' => $this->optionsPageSlug
                    ]]]
                ]);
            }
        }
    }

    public function adminNotices(): void
    {

        // check if WooCommerce is installed and active
        if(!$this->checkIfPluginIsInstalled('woocommerce/woocommerce.php')){
            echo $this->getNoticeHtml(__('requires WooCommerce to be installed and active.', 'wps-price-calculation-formula'));
        }

        // check if ACF Pro is installed and active
        if(!$this->checkIfPluginIsInstalled('advanced-custom-fields-pro/acf.php')){
            echo $this->getNoticeHtml(__('requires ACF Pro to be installed and active.', 'wps-price-calculation-formula'));
        }
    }

    /**
     * Get the html for a notice in the wp-admin panel
     * @param string $message
     * @return string
     */
    private function getNoticeHtml(string $message): string
    {
        return '<div class="notice notice-error is-dismissible"><p><strong>'.$this->name.'</strong> ' . $message . '</p></div>';
    }

    /**
     * Check if a plugin is installed and active
     * example for $pluginName: 'woocommerce/woocommerce.php'
     * @param string $pluginName
     * @return bool
     */
    private function checkIfPluginIsInstalled($pluginName): bool
    {
        return in_array($pluginName, $this->activePlugins);
    }

}