<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Image CMS
 * Sample Module Admin
 */
class Admin extends \BaseAdminController {

    private $template = '';
    private $mainTpl = '';

    public function __construct() {
        parent::__construct();
        /** Load model * */
        $this->load->model('stats_model');


        /**         * */
        /** Prepare template, load scripts and styles * */
        $this->mainTpl = \CMSFactory\assetManager::create()
                ->registerScript('scripts');


        if ($this->input->get('notLoadMain') != 'true') {
            $this->mainTpl
                    ->registerStyle('style')
                    ->registerStyle('nvd3/nv.d3')
                    ->registerScript('nvd3/lib/d3.v3', FALSE, 'before')
                    ->registerScript('nvd3/nv.d3.min', FALSE, 'before')->renderAdmin('main', true);
            /*
              ->registerScript('nvd3/nv.d3', FALSE, 'before')
              ->registerScript('nvd3/stream_layers', FALSE, 'before');
              }

              public function index() {
              /* $data = array();
              $data['brands'] = \mod_stats\classes\Products::getInstance()->getAllBrands();
              \CMSFactory\assetManager::create()
              ->setData($data)
              ->registerStyle('products')
              ->registerScript('products')
              ->renderAdmin('products'); *

              $counts = \mod_stats\models\ProductsBase::getInstance()->getAllCategories();

             */
        }
    }

    public function index() {

        \mod_stats\classes\BaseStats::create()->test();
    }

    /**
     * Load stats template by action 
     * @param string $action
     */
    public function orders($action = '') {
        switch ($action) {
            case 'data':
                $this->template = 'orders/data';
                break;

            case 'price':
                $this->template = 'orders/price';
                break;

            case 'brands_and_cat':
                $this->template = 'orders/brands_and_cat';
                break;

            default:
                $this->template = 'orders/data';
                break;
        }

        $templateData = \CMSFactory\assetManager::create()
                ->setData(array('$data' => $data))
                ->fetchAdminTemplate($this->template, TRUE);

        echo $templateData;
    }

    public function prepareOrdersData($param) {
        
    }

}