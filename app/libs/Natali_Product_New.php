<?php
require_once(ABSPATH . 'wp-load.php');
require_once(ABSPATH . 'wp-includes/wp-db.php');
require_once(ABSPATH . 'wp-admin/includes/taxonomy.php');

/**
 * Основной класс для работы с товарами натали
 *
 * @var $natali_api Natali_Api_DataBase
 * @var $wc_api Natali_Api_WooCommerce
 * @var $settings Natali_Model_Settings
 * @var $attributes Natali_Model_Attributes
 * @var $products Natali_Model_ImportedProduct
 * @var $product_temp Natali_Model_ImportTemp
 * @var $categories Natali_Model_Categories
 */
class Natali_Product_New
{
    /*
     * Данные о товаре натали
     */
    private $id;               // Id товара из натали или артикул woocommerce
    private $wc_id;            // Id товара из woocommerce
    private $data;             // Данные товара из базы натали
    private $wc_data = [];     // Массив данных для woocommerce импорта обновления и т.д.
    private $wc_object;        // Данные уже созданного товара woocommerce
    private $variations;       // Массив вариаций товара
    private $price_type;       // Тип цены товара
    private $product_type;     // Тип товара вариативный или обычный
    private $wc_category_id;   // Категория товара в woocommerce

    /*
     * Модули подключения апи
     */
    private $natali_api;
    private $wc_api;

    /*
     * Модели баз данных
     */
    private $settings;
    private $attributes;
    private $products;
    private $product_temp;
    private $categories;

    /**
     * @throws ErrorException
     */
    public function __construct($product, $wc_cat_id = null)
    {
        // Инициализация контролеров и моделей
        $this->natali_api = new Natali_Api_DataBase();
        $this->product_temp = new Natali_Model_ImportTemp();
        $this->wc_api = new Natali_Api_WooCommerce();
        $this->settings = new Natali_Model_Settings();
        $this->attributes = new Natali_Model_Attributes();
        $this->products = new Natali_Model_ImportedProduct();
        $this->categories = new Natali_Model_Categories();

        // Записываем ID натали товара
        $this->id = (int)$product;

        // Получаем данные о товаре из Натали
        $this->data = $this->natali_api->get("product/get?productId={$this->id}")['data']['product'] ?? null;

        // Получаем тип цены из настроек
        $this->price_type = $this->settings->get('price_type');

        // Получаем тип товара из настроек
        $this->product_type = $this->settings->get('product_type') ?? 'variable';

        // Проверяем существует ли товар если да то мы получи ID woocommerce
        $this->wc_id = wc_get_product_id_by_sku($this->id);

        // Получаем данные от Woocommerce
        if ($this->wc_id) {
            $this->wc_object = $this->wc_api->get("products/$this->wc_id");
        }

        if (!is_null($wc_cat_id)) {
            // Если указана категория, то записываем ее
            $this->wc_category_id = $wc_cat_id;
        } else {
            // Если не указана категория, то получаем из настроек импорта
            $this->wc_category_id = $this->set_wc_cat_id();
        }
    }

    /**
     * Возвращает массив данных от натали
     * @return mixed|null
     */
    public function get_data()
    {
        return $this->data;
    }

    /**
     * Возвращает ID натали
     * @return mixed|null
     */
    public function get_natali_id(): int
    {
        return $this->id;
    }

    /**
     * Установить категорию товара из настроек импорта
     * @return int|mixed
     */
    public function set_wc_cat_id()
    {
        if (isset($this->data['categoriesIds'][0])) {
            $natali_cat = $this->data['categoriesIds'][0];
            return $this->categories->getRowByField('natali_id', $natali_cat)['wc_id'] ?? 0;
        }
        return 0;
    }

    /**
     * Получить id категории woocommerce
     * @return int|mixed
     */
    public function get_wc_cat_id()
    {
        return $this->wc_category_id;
    }

    public function get_wc_data()
    {
        return $this->wc_object;
    }

    /**
     *
     *
     * @param $price
     * @param $percent
     * @return string
     */
    public function add_percent($price, $percent)
    {
        return (string)ceil($price + ($price * $percent / 100));
    }

    /**
     *
     *
     * @param $price
     * @param $percent
     * @return string
     */
    public function subtract_percent($price, $percent)
    {
        return (string)ceil((int)$price - ((int)$price * ((int)$percent / 100)));
    }

    /**
     * Устанавливаем основные данные товара
     *
     * @return void
     * @throws ErrorException
     */
    public function init_wc_data()
    {
        // Добавляем процент к основной цене
        $price = $this->add_percent($this->data[$this->price_type], $this->settings->get('regular_price_margin'));

        // Добавляем процент к цене распродажи
        $sale_price = $this->subtract_percent($price, $this->settings->get('sale_price_margin'));

        // Добавляем основную информацию
        $this->wc_data = [
            'name'              => $this->data['title'],
            'type'              => $this->product_type,
            'regular_price'     => $price,
            'sale_price'        => $sale_price,
            'description'       => $this->data['descriptionText'],
            'short_description' => $this->data['shortDescriptionText'] ?? '',
            'sku'               => (string)$this->data['productId'],
            'categories'        => [['id' => $this->wc_category_id]]
        ];

        // Добавляем атрибут натали
        $this->wc_data['attributes'][] = [
            'id'        => $this->attributes->getIdBySlug('natali_is_natali'),
            'position'  => 0,
            'visible'   => false,
            'variation' => false,
            'options'   => ['Y']
        ];

        // Добавляем атрибут лейбл
        if (isset($this->data['labels'])) {
            $labels = [];
            foreach ($this->data['labels'] as $label) {
                $labels[] = $label;
            }

            $this->wc_data['attributes'][] = [
                'id'        => $this->attributes->getIdBySlug('natali_labels'),
                'visible'   => $this->settings->get('labels') === 'Y',
                'variation' => false,
                'options'   => $labels,
            ];
        }

        // Добавляем атрибут минимальный размер
        if (isset($this->data['minSize'])) {
            $this->wc_data['attributes'][] = [
                'id'        => $this->attributes->getIdBySlug('natali_minsize'),
                'visible'   => $this->settings->get('minSize') === 'Y',
                'variation' => false,
                'options'   => $this->data['minSize'],
            ];
        }

        // Добавляем атрибут максимальный размер
        if (isset($this->data['maxSize'])) {
            $this->wc_data['attributes'][] = [
                'id'        => $this->attributes->getIdBySlug('natali_maxsize'),
                'visible'   => $this->settings->get('maxSize') === 'Y',
                'variation' => false,
                'options'   => $this->data['maxSize'],
            ];
        }

        // Добавляем атрибут состав
        if (isset($this->data['composition'])) {

            $options = explode(',', $this->data['composition']);
            $this->wc_data['attributes'][] = [
                'id'        => $this->attributes->getIdBySlug('natali_composition'),
                'visible'   => $this->settings->get('composition') === 'Y',
                'variation' => false,
                'options'   => $options,
            ];
        }

        // Добавляем атрибут материал
        if ($this->data['materials']) {
            $materials = [];

            foreach ($this->data['materials'] as $material) {
                $materials[] = $material['title'];
            }

            $this->wc_data['attributes'][] = [
                'id'        => $this->attributes->getIdBySlug('natali_materials'),
                'name'      => 'Материал',
                'visible'   => true,
                'variation' => false,
                'options'   => $materials,
            ];
        }

        // Добавляем атрибут брэнд
        if (isset($this->data['brandId'])) {
            $brand_list = $this->natali_api->get('brand/list')['brands'] ?? null;

            if (is_array($brand_list)) {
                $brand = array_filter($brand_list, function ($brand) {
                    return $brand['id'] === $this->data['brandId'];
                });
            }

            if (isset($brand[0]['title']) && $brand[0]['title']) {
                $this->wc_data['attributes'][] = [
                    'id'        => $this->attributes->getIdBySlug('natali_brands'),
                    'visible'   => $this->settings->get('brand') === 'Y',
                    'variation' => false,
                    'options'   => $brand[0]['title'],
                ];
            }
        }

        // Добавляем атрибут видео
        if (isset($this->data['videos'])) {
            $videos = [];

            foreach ($this->data['videos'] as $video) {
                $videos[] = $video['url'];
            }

            $this->wc_data['attributes'][] = [
                'name'      => 'Видео',
                'visible'   => $this->settings->get('video') === 'Y',
                'variation' => false,
                'options'   => $videos
            ];
        }

        /*
         * Устанавливаем цвета и размеры для товара
         */
        if ($this->data['colors']) {
            $colors = [];
            $sizes = [];
            $onlySizes = count($this->data['colors']) == 1 && $this->data['colors'][0]['colorId'] === 0;

            if (!$onlySizes) {
                foreach ($this->data['colors'] as $color) {
                    // Создаем один цвет и записываем в него colorId от натали
                    if ($color['colorId']) {            // Проверяем цвет не в ассортименте ли
                        $colors[] = $color['title'];    // Устанавливаем название цвета

                        // Устанавливаем мета поле colorId для хранения
                        $color_term = wp_create_term($color['title'], 'pa_natali_color');
                        update_term_meta($color_term['term_id'], 'colorId', $color['colorId']);

                        foreach ($color['sizes'] as $size) {
                            $sizes[] = $size['title']; // Устанавливаем название размера

                            // Устанавливаем мета поле colorId для хранения
                            $sizes_term = wp_create_term($size['title'], 'pa_natali_size');
                            update_term_meta($sizes_term['term_id'], 'sizeId', $size['sizeId']);
                        }
                    }
                }
            } else {
                foreach ($this->data['colors'][0]['sizes'] as $size) {
                    $sizes[] = $size['title'];

                    // Устанавливаем мета поле sizeId для хранения
                    $sizes_term = wp_create_term($size['title'], 'pa_natali_size');
                    update_term_meta($sizes_term['term_id'], 'sizeId', $size['colorId']);
                }
            }

            if (!$onlySizes) {
                // Устанавливаем атрибут цвета
                $this->wc_data['attributes'][] = [
                    'id'        => $this->attributes->getIdBySlug('natali_color'),
                    'position'  => 0,
                    'name'      => 'Цвет',
                    'visible'   => true,
                    'variation' => true,
                    'options'   => $colors
                ];
            }

            // Устанавливаем атрибут размера
            $this->wc_data['attributes'][] = [
                'id'        => $this->attributes->getIdBySlug('natali_size'),
                'position'  => 0,
                'name'      => 'Размер',
                'visible'   => true,
                'variation' => true,
                'options'   => $sizes
            ];

            if (!$onlySizes) {
                // Устанавливаем атрибут размера
                $this->data['default_attributes'] = [
                    [
                        'id'     => $this->attributes->getIdBySlug('natali_color'),
                        'option' => $colors[0] ?? null
                    ],
                    [
                        'id'     => $this->attributes->getIdBySlug('natali_size'),
                        'option' => $sizes[0] ?? null
                    ]
                ];
            } else {
                $this->data['default_attributes'] = [
                    [
                        'id'     => $this->attributes->getIdBySlug('natali_size'),
                        'option' => $sizes[0] ?? null
                    ]
                ];
            }
        }

    }

    /**
     * @throws Exception
     */
    public function update()
    {
        // Проверяем существует ли товар
        if (!$this->wc_id) {
            if ($this->wc_data) {
                // Создаем товар если его нет, а данные в натали есть
                $create = $this->create();
            }
        } else {
            // Устанавливаем данные для woocommerce
            $this->init_wc_data();

            // Получаем изображения для товара
            $this->get_images();

            // Отправляем запрос на изменение товара
            $this->wc_object = $this->wc_api->put("products/{$this->wc_id}", $this->wc_data);

            // Удаляем старые вариации
            $deleted = $this->delete_variations();

            // Добавляем вариации товара
            if ($this->product_type === 'variable') {
                $this->create_variations();
            }

            // Устанавливаем атрибуты по умолчанию для плагинов более старой версии
            $response = $this->wc_api->put(
                "products/$this->wc_id",
                ['default_attributes' => $this->data['default_attributes']]);

            if ($response) {
                // Обновляем поле с датой и временем обновления плагина
                update_post_meta($this->wc_object->id, 'natali_update', date('Y-m-d h:i:s'));
            }
        }

        // Возвращаем данные от woocommerce
        return $this->wc_object;
    }

    public function delete()
    {
        $images = [];
        foreach ($this->wc_object->images as $image) {
            $images[] = $image->id;
        }

        $result = $this->wc_api->delete("products/{$this->wc_id}", [
            'force' => true
        ]);

        if ($images) {
            //Удаляем фотографии
            foreach ($images as $image) {
                wp_delete_attachment($image, true);
            }
        }

        if ($result) {
            Natali_Handler::success('Удаление товара', 'Успешно', [
                'id'      => $result->id,
                'name'    => $result->name,
                'article' => $result->sku
            ]);

            return true;
        }

        return false;
    }

    /**
     *
     * @throws ErrorException
     */
    public function delete_variations()
    {
        $variations = $this->wc_api->get("products/$this->wc_id/variations");

        $delete = [];
        foreach ($variations as $variation) {
            $delete[] = $variation->id;
        }

        return $this->wc_api->post("products/$this->wc_id/variations/batch", [
            'delete' => $delete
        ]);
    }

    /**
     * Добавляем поля с информацией о товаре natali
     */
    public function init_product_meta()
    {
        if ($this->data && $this->wc_object !== null) {
            // Добавляем поле содержащее id натали
            update_post_meta($this->wc_object->id, 'natali_id', $this->data['productId']);

            // Добавляем поле содержащее все данные о товаре с сайта натали
            update_post_meta($this->wc_object->id, 'natali_data', $this->data);

            // Добавляем поле с датой и временем когда плагин обновил данные о товаре
            update_post_meta($this->wc_object->id, 'natali_update', date('Y-m-d h:i:s'));
        }
    }

    /**
     * Устанавливаем изображения для товара
     */
    public function get_images()
    {
        // Проверяем есть изображения
        if ($this->data['images']) {
            // Устанавливаем тип изображений сжатые или нет
            $imageType = $this->settings->get('images') ?? 'previewUrl';

            $imagesList = $this->data['images'];

            $imagesList[] = [
                'url'            => $this->data['imageUrl'],
                'previewUrl'     => $this->data['previewImageUrl'],
                'colorId'        => 0,
                'mainColorImage' => true,
            ];

            // Сохраняем фотографии на сервер и записываем массив ID фотографий
            $images = Natali_Files::saveFiles($imagesList, $imageType, $this->wc_id ?? 0);

            if ($images) {
                // Прикрепляем фотографии к товару по id
                foreach ($images as $key => $imgId) {
                    $this->wc_data['images'][$key] = [
                        'id' => $imgId
                    ];
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public function create()
    {
        // Проверяем существует ли уже товар
        if ($this->wc_id) {
            $this->product_temp->setProductStatus($this->id, 1);
            throw new Exception("Error: Товар уже существует [$this->id]", 400);
        }

        // Проверяем получили ли мы данные от натали
        if (!$this->data) {
            $this->product_temp->delete($this->id); // Удаляем товар из очереди
            throw new Exception("Error: Нету данных о товаре [$this->id]", 400);
        }


        // Подготавливаем данные для woocommerce
        $this->init_wc_data();

        // Проверяем есть ли данные для woocommerce
        if (is_null($this->wc_data)) {
            throw new Exception("Error: Нет данных для создания товара [$this->id]", 400);
        }

        if ($this->wc_category_id) {
            // Создаем товар
            $this->wc_object = $this->wc_api->post('products', $this->wc_data);

            // Записываем id товара
            $this->wc_id = $this->wc_object->id;

            // Загружаем фотографии товара
            $this->get_images();

            // Закрепляем фотографии к товару
            $this->wc_object = $this->wc_api->put("products/$this->wc_id", $this->wc_data);

            if ($this->wc_object) {
                // Добавляем мета данные для товара
                $this->init_product_meta();

                // Добавляем вариации товара
                if ($this->product_type === 'variable') {
                    $this->create_variations();
                }

                $newProduct = [
                    'product_id'     => $this->id,
                    'wc_id'          => $this->wc_object->id,
                    'wc_category_id' => $this->wc_category_id
                ];

                $this->products->insert($newProduct);
                $this->product_temp->setProductStatus($this->id, 1);
                return true;
            }
        }
        return false;
    }

    /**
     * Создаем вариации товара
     */
    public function create_variations()
    {
        if ($this->data['colors']) {
            // Инициализируем массив для создания вариаций пачкой
            $data = ['create' => []];

            // Проверяем есть ли цвета у товара
            $onlySizes = count($this->data['colors']) == 1 && $this->data['colors'][0]['colorId'] === 0;

            if (!$onlySizes) {
                // Если цвета есть
                foreach ($this->data['colors'] as $color) {
                    // Получаем ID изображения для цвета
                    $idImage = $this->get_image_for_variation($color['colorId']);

                    // Перебираем все размеры для цвета
                    foreach ($color['sizes'] as $size) {

                        // Рассчитываем цену для вариации товара
                        $price = $this->add_percent(
                            $size[$this->price_type],
                            $this->settings->get('regular_price_margin')
                        );

                        // Рассчитываем цену распродажи для вариации товара
                        $sale_price = $this->subtract_percent($price, $this->settings->get('sale_price_margin'));

                        // Добавляем вариацию в массив всех вариаций
                        $data['create'][] = [
                            'regular_price' => $price,      // Устанавливаем цену
                            'sale_price'    => $sale_price, // Устанавливаем цену распродажи
                            'image'         => [
                                'id' => $idImage,           // Устанавливаем id картинку вариации
                            ],
                            'attributes'    => [            // Указываем атрибуты в вариации
                                [
                                    'id'     => $this->attributes->getIdBySlug('natali_color'),
                                    'option' => $color['title']
                                ],
                                [
                                    'id'     => $this->attributes->getIdBySlug('natali_size'),
                                    'option' => $size['title']
                                ]
                            ]
                        ];
                    }
                }
            } else {
                // Если цвета нету
                foreach ($this->data['colors'][0]['sizes'] as $size) {
                    // Рассчитываем цену
                    $price = $this->add_percent($size[$this->price_type], $this->settings->get('regular_price_margin'));

                    // Рассчитываем цену распродажи
                    $sale_price = $this->subtract_percent($price, $this->settings->get('sale_price_margin'));

                    // Добавляем в массив вариацию
                    $data['create'][] = [
                        'regular_price' => $price,        // Устанавливаем цену
                        'sale_price'    => $sale_price,   // Устанавливаем цену распродажи
                        'attributes'    => [              // Указываем размер для вариации
                            [
                                'id'     => $this->attributes->getIdBySlug('natali_size'),
                                'option' => $size['title']
                            ]
                        ]
                    ];
                }
            }

            // Создаем вариации товара и записываем их
            $this->variations = $this->wc_api->post("products/{$this->wc_object->id}/variations/batch", $data);

            // Устанавливаем вариацию по умолчанию
            $this->wc_api->put("products/$this->wc_id", ['default_attributes' => $this->data['default_attributes']]);
        }
    }

    /**
     * Получить изображение для вариации
     * @param $colorId
     * @return null
     */
    public function get_image_for_variation($colorId)
    {
        $type = $this->settings->get('images');
        foreach ($this->data['images'] as $key => $item) {
            if ($item['colorId'] == $colorId && $item['mainColorImage']) {
                if ($type === 'previewUrl') {
                    $url = str_replace(
                        'https://static.natali37.ru/',
                        'https://static.natali37.ru/media/900/',
                        $item['url']);
                } else {
                    $url = $item['url'];
                }

                return $this->wc_data['images'][crc32($url)]['id'];
            }
        }

        return null;
    }

    /**
     * Получить объект товара woocommerce
     *
     * @return mixed
     */
    public function get_wc_object()
    {
        return $this->wc_object;
    }

    /**
     * Получить ID woocommerce
     *
     * @return mixed
     */
    public function get_wc_id()
    {
        if ($this->wc_object) {
            return $this->wc_object->id;
        }

        if ($this->wc_id) {
            return $this->wc_object = $this->wc_api->get("products/$this->wc_id");
        }

        return false;
    }
}