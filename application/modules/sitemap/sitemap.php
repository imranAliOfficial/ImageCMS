<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Image CMS
 *
 * Sitemap Module
 * @property sitemap_model $sitemap_model
 */
class Sitemap extends MY_Controller {

    /**
     * Priority for pages
     * @var int
     */
    public $pages_priority = '0.6';

    /**
     * Priority for categories
     * @var int 
     */
    public $cats_priority = '0.8';

    /**
     * Priority for main page
     * @var int 
     */
    public $main_page_priority = '1';

    /**
     * Priority for subcategories pages
     * @var int 
     */
    public $sub_cats_priority = '0.7'; // priority for subcategories pages
    /**
     * Priority for products pages
     * @var int 
     */
    public $products_priority = '0.5';

    /**
     * Priority for products categories pages
     * @var int 
     */
    public $products_categories_priority = '0.4';

    /**
     * Priority for products sub categories pages
     * @var int 
     */
    public $products_sub_categories_priority = '0.4';

    /**
     * Priority for brands pages
     * @var int 
     */
    public $brands_priority = '0.4';

    /**
     * Frequency for pages
     * @var type 
     */
    public $pages_changefreq = 'daily';

    /**
     * Frequency for categories pages
     * @var string 
     */
    public $categories_changefreq = 'weekly';

    /**
     * Frequency for products categories pages
     * @var string 
     */
    public $products_categories_changefreq = 'weekly';

    /**
     * Frequency for products sub categiries pages
     * @var string 
     */
    public $products_sub_categories_changefreq = 'weekly';

    /**
     * Frequency for main page
     * @var string 
     */
    public $main_page_changefreq = 'daily';

    /**
     * Frequency for products pages
     * @var string 
     */
    public $products_changefreq = 'weekly';

    /**
     * Frequency for brands pages
     * @var string 
     */
    public $brands_changefreq = 'weekly';

    /**
     * Frequency for sub categories pages
     * @var string 
     */
    public $sub_categories_changefreq = 'weekly';

    /**
     * Blocked urls array
     * @var array 
     */
    public $blocked_urls = array();

    /**
     * Default frequency
     * @var string 
     */
    public $changefreq = 'daily';

    /**
     * Gzip level
     * @var type 
     */
    public $gzip_level = 0;

    /**
     * Sitemap result
     * @var type 
     */
    public $result = '';

    /**
     * Langs array
     * @var type 
     */
    public $langs = array();

    /**
     * Default lang
     * @var type 
     */
    public $default_lang = array();
    public $sitemap_ttl = 3600;

    /**
     * Sitemap key
     * @var type 
     */
    public $sitemap_key = 'sitemap_';

    /**
     * Updated page url
     * @var string
     */
    private $updated_url = '';

    /**
     * Path to saved sitemap file
     * @var string
     */
    private $sitemap_path = './application/modules/sitemap/map/sitemap.xml';

    /**
     * Sitemap items
     * @var array 
     */
    public $items = array();

    function __construct() {
        parent::__construct();
        $lang = new MY_Lang();
        $lang->load('sitemap');
        $this->robots = $this->replace(file('robots.txt'));
        $this->load->module('core');
        $this->load->model('sitemap_model');
        $this->langs = $this->core->langs;
        $this->default_lang = $this->core->def_lang[0];
        if (uri_string() == 'sitemap.xml') {
            $this->build_xml_map();
            exit();
        }
    }

    /**
     * Show sitemap for categories
     */
    public function index() {
        $categories = $this->lib_category->_build();

        $this->template->assign('content', $this->sitemap_ul($categories));
        $this->template->show();
    }

    public static function adminAutoload() {
        parent::adminAutoload();

        // Set listeners on page pre update to set url
        \CMSFactory\Events::create()->setListener('setUpdatedUrl', 'ShopAdminProducts:preEdit');
        \CMSFactory\Events::create()->onAdminPagePreEdit()->setListener('setUpdatedUrl');
        \CMSFactory\Events::create()->onAdminCategoryPreUpdate()->setListener('setUpdatedUrl');
        \CMSFactory\Events::create()->onShopCategoryPreEdit()->setListener('setUpdatedUrl');
        \CMSFactory\Events::create()->onShopBrandPreEdit()->setListener('setUpdatedUrl');

        \CMSFactory\Events::create()->onShopProductCreate()->setListener('ping_google');
        \CMSFactory\Events::create()->onShopProductUpdate()->setListener('ping_google');
        \CMSFactory\Events::create()->onShopProductDelete()->setListener('ping_google');

        \CMSFactory\Events::create()->onShopCategoryCreate()->setListener('ping_google');
        \CMSFactory\Events::create()->onShopCategoryEdit()->setListener('ping_google');
        \CMSFactory\Events::create()->onShopCategoryDelete()->setListener('ping_google');

        \CMSFactory\Events::create()->onShopBrandCreate()->setListener('ping_google');
        \CMSFactory\Events::create()->onShopBrandEdit()->setListener('ping_google');
        \CMSFactory\Events::create()->onShopBrandDelete()->setListener('ping_google');

        \CMSFactory\Events::create()->onAdminPageCreate()->setListener('ping_google');
        \CMSFactory\Events::create()->onAdminPageUpdate()->setListener('ping_google');
        \CMSFactory\Events::create()->onAdminPageDelete()->setListener('ping_google');

        \CMSFactory\Events::create()->onAdminCategoryCreate()->setListener('ping_google');
        \CMSFactory\Events::create()->onAdminCategoryUpdate()->setListener('ping_google');
    }

    /**
     * Set page url when page item is updated
     * @param type $data - events array that contains page url $data['url'] 
     * @return boolean
     */
    public function setUpdatedUrl($data) {
        $ci = & get_instance();
        if ($data) {
            if ($data['url']) {
                $ci->updated_url = $data['url'];
                return TRUE;
            }
        }
    }

    /**
     * Initialize module settings
     * @param array $settings
     */
    public function initialize() {
        // Get sitemap values
        $priorities = $this->sitemap_model->getPriorities();
        $changfreq = $this->sitemap_model->getChangefreq();
        $blocked_urls = $this->sitemap_model->getBlockedUrls();

        // Initialize priorities
        if ($priorities) {
            $this->main_page_priority = $priorities['main_page_priority'];
            $this->cats_priority = $priorities['cats_priority'];
            $this->pages_priority = $priorities['pages_priority'];
            $this->sub_cats_priority = $priorities['sub_cats_priority'];
            $this->products_priority = $priorities['products_priority'];
            $this->products_categories_priority = $priorities['products_categories_priority'];
            $this->products_sub_categories_priority = $priorities['products_sub_categories_priority'];
            $this->brands_priority = $priorities['brands_priority'];
        }

        // Initialize changfreq
        if ($changfreq) {
            $this->main_page_changefreq = $changfreq['main_page_changefreq'];
            $this->categories_changefreq = $changfreq['categories_changefreq'];
            $this->products_categories_changefreq = $changfreq['products_categories_changefreq'];
            $this->products_sub_categories_changefreq = $changfreq['products_sub_categories_changefreq'];
            $this->pages_changefreq = $changfreq['pages_changefreq'];
            $this->products_changefreq = $changfreq['product_changefreq'];
            $this->brands_changefreq = $changfreq['brands_changefreq'];
            $this->sub_categories_changefreq = $changfreq['sub_categories_changefreq'];
        }

        // Initialize Blocked urls
        if ($blocked_urls) {
            foreach ($blocked_urls as $url) {
                $this->blocked_urls[] = $url['url'];
            }
        }
    }

    /**
     * Display sitemap ul list
     */
    public function sitemap_ul($items = array()) {
        $out .= '<ul id="sitemap">';

        foreach ($items as $item) {
            if (isset($item['path_url'])) {
                $url = $item['path_url'];
            } elseif (isset($item['full_url'])) {
                $url = $item['full_url'];
            }

            $out .= '<li>' . anchor($url, $item['name']) . '</li>';

            // Get category pages
            if (isset($item['path_url'])) {
                $pages = $this->sitemap_model->get_cateogry_pages($item['id']);

                if ($pages) {
                    $out .= $this->sitemap_ul($pages);
                }
            }

            if (count($item['subtree']) > 0) {
                $out .= $this->sitemap_ul($item['subtree']);
            }
        }

        $out .= '</ul>';

        return $out;
    }

    /**
     * Create and display sitemap xml
     */
    public function build_xml_map() {
        $settings = $this->sitemap_model->load_settings();

        if ($settings['generateXML']) {
            $this->_create_map();
        } else {
            $this->result = file_get_contents($this->sitemap_path);
        }

        if ($this->result) {
            header("content-type: text/xml");
            echo $this->result;
        }
    }

    /**
     * Create map
     */
    public function _create_map() {
        $this->initialize();

        // Add main page
        if (!$this->robotsCheck(site_url())) {
            $this->items[] = array(
                'loc' => site_url(),
                'changefreq' => $this->main_page_changefreq,
                'priority' => $this->main_page_priority
            );
        }

        // Add categories to sitemap urls.
        $categories = $this->lib_category->unsorted();

        foreach ($categories as $category) {
            if (!$this->robotsCheck(site_url($category['path_url']))) {

                if ((int) $category['parent_id']) {
                    $changefreq = $this->sub_categories_changefreq;
                    $priority = $this->sub_cats_priority;
                } else {
                    $changefreq = $this->categories_changefreq;
                    $priority = $this->cats_priority;
                }

                if ($this->not_blocked_url($category['path_url'])) {
                    $this->items[] = array(
                        'loc' => site_url($category['path_url']),
                        'changefreq' => $changefreq,
                        'priority' => $priority
                    );
                }

                // Add links to categories in all langs.
                foreach ($this->langs as $k => $v) {
                    if ($v['id'] != $this->default_lang['id']) {
                        if ($this->not_blocked_url($k . '/' . $category['path_url'])) {
                            $this->items[] = array(
                                'loc' => site_url($k . '/' . $category['path_url']),
                                'changefreq' => $changefreq,
                                'priority' => $priority
                            );
                        }
                    }
                }
            }
        }

        // Get all pages
        $pages = $this->sitemap_model->get_all_pages();

        foreach ($pages as $page) {

            if (!$this->robotsCheck($page['full_url'])) {

                // create page url
                if ($page['lang'] == $this->default_lang['id']) {
                    $url = site_url($page['full_url']);
                    $url_page = $page['full_url'];
                } else {
                    $prefix = $this->_get_lang_prefix($page['lang']);
                    $url = site_url($prefix . '/' . $page['full_url']);
                    $url_page = $prefix . '/' . $page['full_url'];
                }

                // create date
                if ($page['updated'] > 0) {
                    $date = date('Y-m-d', $page['updated']);
                } else {
                    $date = date('Y-m-d', $page['created']);
                }
                $c_priority = $this->cats_priority;
                if ($page['cat_url'] == '') {
                    $c_priority = $this->cats_priority;
                } else {
                    $c_priority = $this->pages_priority;
                }

                if ($this->not_blocked_url($url_page)) {
                    $this->items[] = array(
                        'loc' => $url,
                        'lastmod' => $date,
                        'changefreq' => $this->pages_changefreq,
                        'priority' => $c_priority
                    );
                }
            }
        }


        if (SHOP_INSTALLED) {

            $shop_categories = $this->sitemap_model->get_shop_categories();
            foreach ($shop_categories as $shopcat) {
                $url = 'shop/category/' . $shopcat['full_path'];
                if ($this->not_blocked_url($url)) {
                    $url = site_url($url);
                    if (!$this->robotsCheck($url)) {

                        if ((int) $shopcat['parent_id']) {
                            $changefreq = $this->products_sub_categories_changefreq;
                            $priority = $this->products_sub_categories_priority;
                        } else {
                            $changefreq = $this->products_categories_changefreq;
                            $priority = $this->products_categories_priority;
                        }

                        $this->items[] = array(
                            'loc' => site_url('shop/category/' . $shopcat['full_path']),
                            'lastmod' => '',
                            'changefreq' => $changefreq,
                            'priority' => $priority
                        );
                    }
                }
            }

            $shop_brands = $this->sitemap_model->get_shop_brands();
            foreach ($shop_brands as $shopbr) {
                $url = site_url('shop/brand/' . $shopbr['url']);
                if ($this->not_blocked_url('shop/brand/' . $shopbr['url'])) {
                    if (!$this->robotsCheck($url)) {
                        $this->items[] = array(
                            'loc' => $url,
                            'lastmod' => '',
                            'changefreq' => $this->brands_changefreq,
                            'priority' => $this->brands_priority,
                        );
                    }
                }
            }

            $shop_products = $this->sitemap_model->get_shop_products();
            foreach ($shop_products as $shopprod) {
                $url = site_url('shop/product/' . $shopprod['url']);
                if ($this->not_blocked_url('shop/product/' . $shopprod['url'])) {
                    if (!$this->robotsCheck($url)) {
                        if ($shopprod['updated'] > 0) {
                            $date = date('Y-m-d', $shopprod['updated']);
                        } else {
                            $date = date('Y-m-d', $shopprod['created']);
                        }
                        $this->items[] = array(
                            'loc' => $url,
                            'lastmod' => $date,
                            'changefreq' => $this->products_changefreq,
                            'priority' => $this->products_priority,
                        );
                    }
                }
            }
        }
        $this->result = $this->generate_xml($this->items);
        return $this->result;
//$this->cache->store($this->sitemap_key, $this->result, $this->sitemap_ttl);
    }

    /**
     * Chech is url blocked
     * @param string $url
     * @return boolean
     */
    private function not_blocked_url($url) {
        if (!in_array($url, $this->blocked_urls) && !in_array(substr($url, 0, -1), $this->blocked_urls)) {
            foreach ($this->blocked_urls as $blocked_url) {
                $url = str_replace(site_url(), '', $url);

                if (mb_strpos($url, '/') === 0) {
                    $url = substr($url, 1);
                }

                if (mb_strrpos($url, '/') === (mb_strlen($url) - 1)) {
                    $url = substr($url, 0, -1);
                }

                $url_length = mb_strlen($blocked_url);
                $last_symbol = substr($blocked_url, $url_length - 1);
                $first_symbol = substr($blocked_url, 0, 1);
                $blocked_url_tpm = str_replace('*', '', $blocked_url);

                if ($last_symbol == '*') {
                    $url_position = mb_strpos($url, $blocked_url_tpm);
                    if ($url_position === 0) {
                        return FALSE;
                    }
                }

                if ($first_symbol == '*') {
                    $must_be_in_pos = (int) mb_strlen($url) - (int) mb_strlen($blocked_url_tpm);
                    $url_position = mb_strrpos($url, $blocked_url_tpm);
                    if ($must_be_in_pos == $url_position) {
                        return FALSE;
                    }
                }
            }

            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Generate xml
     * @param array $items
     * @return string
     */
    private function generate_xml($items = array()) {
        $data = '';

        while ($item = current($items)) {
            $data .= "<url>\n";
            foreach ($item as $k => $v) {
                if ($v != '') {
                    $data .= "\t<$k>" . htmlspecialchars($v) . "</$k>\n";
                }
            }
            $data .= "</url>\n";

            next($items);
        }

        return "<\x3Fxml version=\"1.0\" encoding=\"UTF-8\"\x3F>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n" . $data . "\t</urlset>";
    }

    /**
     * Get language prefix by lang id
     */
    private function _get_lang_prefix($id) {
        foreach ($this->langs as $k => $v) {
            if ($v['id'] === $id) {
                return $k;
            }
        }
    }

    /**
     * Replace robots disalow
     * @param type $lines
     * @return array
     */
    public function replace($lines) {
        $array = array();
        foreach ($lines as $line) {
            if ((substr_count($line, 'Disallow:') > 0) && (trim(str_replace('Disallow:', '', $line)) != ''))
                array_push($array, trim(str_replace('Disallow:', '', $line)));
        }
        return $array;
    }

    /**
     * Check robots 
     * @param type $check
     * @return boolean
     */
    public function robotsCheck($check) {
        $array = $this->robots;
        foreach ($array as $ar) {
            if ($ar == '/')
                return true;

            if (strstr($check, $ar))
                return true;
        }
        return false;
    }

    /**
     * Send xml to google
     * return $code if send (200 = ok) else 'false'
     */
    public function ping_google($data) {
        // Checking is used server is local
        if (strstr($_SERVER['SERVER_NAME'], '.loc'))
            return FALSE;

        $ci = & get_instance();

        $ci->db->select('settings');
        $ci->db->where('name', 'sitemap');
        $query = $ci->db->get('components', 1)->row_array();

        $settings = unserialize($query['settings']);

        // Check if turn off sending site map
        if (!$settings['sendSiteMap'])
            return FALSE;

        // Check sending Site map url is change
        if ($settings['sendWhenUrlChanged']) {
            if ($ci->updated_url) {
                if ($ci->updated_url == $data['url']) {
                    return FALSE;
                }
                unset($ci->updated_url);
            }
        }

        // Checking time permission(1 hour passed from last send) to send ping
        if ((time() - $settings['lastSend']) / (60 * 60) >= 1) {

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "http://www.google.com/webmasters/tools/ping?sitemap=" . site_url() . "/sitemap.xml");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $output = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            if ($code == '200') {
                // Update settings, set lastSend time 
                $settings['lastSend'] = time();
                $ci->db->limit(1);
                $ci->db->where('name', 'sitemap');
                $ci->db->update('components', array('settings' => serialize($settings)));

                showMessage(lang('Ping sended', 'sitemap'), 'Google ping');
            }

            return $code;
        }
        return false;
    }

    /**
     * Gzip generate
     */
    public function gzip() {
        $this->_create_map();
        echo gzencode($this->result, $this->gzip_level);
    }

    /**
     * Install module
     */
    function _install() {
        return $this->sitemap_model->installModule();
    }

    /**
     * Deinstall module
     */
    function _deinstall() {
        return $this->sitemap_model->deinstallModule();
    }

}

/* End of file sitemap.php */
