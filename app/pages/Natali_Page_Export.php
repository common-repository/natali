<?php

class Natali_Page_Export extends Natali_Abstract_Page
{
    public function render($params = []) {
        $this->disable = false;

        $params = [
            'page_title' => __('Экспорт товаров из заказов', 'wp-natali'),
            'head'      => ['Номер заказа', 'Заказчик', 'Дата заказа', 'Сумма заказа'],
            'idForm'    => 'natali_export_form',
            'notices'   => [],
            'disable'   => $this->disable
        ];
        $params['page'] = str_replace('/', '', esc_attr($_GET['page']));

        if (WOOCOMMERCE_STATUS) {
            $params['list'] = $this->get_list_orders();

            if ($params['list']) {
                $params['notices'][] = [
                    'content' => 'Выделите все заказы из которых вы хотите импортировать товары натали',
                    'type'    => 'warning',
                    'class'   => 'notice-response'
                ];
            }
        } else {
            $this->disable = true;

            $params['notices'][] =
                [
                    'content' => 'Пожалуйста активируйте Woocommerce',
                    'type'    => 'error',
                ];

            $params['disable'] = $this->disable;
        }

        if (file_exists(NATALI_PLUGIN_DIR . '/exports/export.csv')) {
            $link = NATALI_PLUGIN_URL . '/exports/export.csv';
            $params['notices'][] = [
                'content' => sprintf('Файл последнего экспорта <a href="%1s">скачать</a>', $link),
                'type'    => 'warning',
                'class'   => 'notice-response'
            ];
        }

        Natali_Template::the('layouts/app-table', $params);
    }

    /**
     * Получаем список всех заказов с сайта
     *
     * @return \stdClass|\WC_Order[]
     * Number of pages and an array of order objects if
     * paginate is true, or just an array of values.
     */
    public function get_orders() {
        return wc_get_orders(
            [
                'limit'       => 0,
                'post_status' => 'processing'
            ]
        );
    }

    /**
     * Создаем список всех заказов для рендера таблицы
     * @return array
     */
    public function get_list_orders() {
        $orders = $this->get_orders();
        $result = [];

        foreach ($orders as $key => $order) {
            $result[$key]['id'] = $order->get_id();
            $result[$key]['client'] = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
            $result[$key]['date'] = $order->get_date_created();
            $result[$key]['total'] = $order->get_total() . ' ' . $order->get_currency();
        }

        return $result;
    }

    /**
     * Метод для экспорта всех товаров из выделеных заказов в таблице,
     * вызываеться через fetch запрос на странице экспорта
     * @return void
     */
    public static function startExport() {
        //Получаем модель для работы с таблицей импортированых товаров
        $listProductImported = new Natali_Model_ImportedProduct();

        //Определяем заголовки файла csv
        $arrayTitle = [
            'product_id'    => 'Код товара',
            'size_id'       => 'Код размера',
            'color_id'      => 'Код цвета',
            'replace_color' => 'Замена цвета',
            'quality'       => 'Количество',
            'comment'       => 'Комментарий'
        ];

        //Инициализируем результатирующий массив
        $result = [];

        //Получаем строку из id заказов и преобразуем в массив
        $orders = explode(',', sanitize_text_field($_POST['orders']));

        //Запускаем цикл перебора всех заказов для получения товаров из них
        foreach ($orders as $orderId) {
            //Получаем данные заказа
            $order = wc_get_order($orderId);

            //Получаем все товары заказа
            $products = $order->get_items();

            //Перебираем все товары в заказе и формируем новую строку в файле
            foreach ($products as $product) {
                //Получаем данные товара из таблицы импортированых товаров
                $natali_product = $listProductImported->getRowByField('wc_id', $product->get_data()['product_id']);
                //Проверяем являеться ли товар товаром натали
                if (isset($natali_product['product_id']) && $natali_product['product_id']) {
                    //Получаем данные атрибутов товара в заказе
                    $attributes = $product->get_meta_data();

                    //Ищем атрибут цвета в массиве атрибутов
                    $color_object = array_filter($attributes, function ($attr) {
                        $data = $attr->get_data();
                        return $data['key'] === "pa_natali_color";
                    });
                    //Получаем данные атрибута цвета из заказа
                    $color = $color_object[0]->jsonSerialize();

                    //Получаем массив данных атрибута цвета такие как term_id taxanomy_id
                    $colorData = wp_create_term($color['value'], 'pa_natali_color');

                    //Ищем атрибут размера в массиве атрибутов
                    $size_object = array_filter($attributes, function ($attr) {
                        $data = $attr->get_data();
                        return $data['key'] === "pa_natali_size";
                    });
                    //Получаем данные атрибута размера из заказа
                    $size = $size_object[1]->jsonSerialize();

                    //Получаем массив данных атрибута размера такие как term_id taxanomy_id
                    $sizeData = wp_create_term($size['value'], 'pa_natali_size');


                    //Создаем новую строку в файле экспорта
                    $result[] = [
                        'product_id'    => $natali_product['product_id'],
                        'size_id'       => get_term_meta($sizeData['term_id'], 'sizeId', true),
                        'color_id'      => get_term_meta($colorData['term_id'], 'colorId', true),
                        'replace_color' => 0,
                        'quality'       => $product->get_quantity(),
                        'comment'       => $product->get_name()
                    ];
                }
            }
        }

        if ($result) {
            //Добавляем строку заголовка в результирующий массив
            array_unshift($result, $arrayTitle);

            //Создаем новый файл csv из результирующего массива
            self::create_csv_file($result, NATALI_PLUGIN_DIR . '/exports/export.csv');

            $file_link = NATALI_PLUGIN_URL . '/exports/export.csv';

            echo json_encode(
                [
                    'status'   => 200,
                    'notice'   => Natali_Template::get('components/notice', [
                        'content' => "Успешно экспортировал <a href='{$file_link}'>ссылка</a> на файл csv",
                        'type'    => 'success',
                        'class'   => 'notice-response'
                    ]),
                    'response' => NATALI_PLUGIN_URL . '/exports/export.csv'
                ]
            );
        } else {
            echo json_encode(
                [
                    'status'   => 404,
                    'notice'   => Natali_Template::get('components/notice', [
                        'content' => 'Нет товаров натали в выбраных заказах',
                        'type'    => 'error',
                        'class'   => 'notice-response'
                    ]),
                    'response' => null,
                ]
            );
        }

        wp_die();
    }

    /**
     * Метод создающий из ассоциативного массива файл csv
     *
     * @param array $create_data Ассоциативныый массив из которого будет создан файл
     * @param string $file Путь куда нужно сохранить файл
     * @param string $col_delimiter Установить какой разделить устанавливать между ячейками ; , :
     * @param string $row_delimiter Символ переноса на новую строку "\r\n"
     * @return false|string
     */
    public static function create_csv_file($create_data, $file = null, $col_delimiter = ';', $row_delimiter = "\r\n") {
        if (!is_array($create_data)) {
            return false;
        }

        if ($file && !is_dir(dirname($file))) {
            return false;
        }

        // строка, которая будет записана в csv файл
        $CSV_str = '';

        // перебираем все данные
        foreach ($create_data as $row) {
            $cols = [];

            foreach ($row as $col_val) {
                // строки должны быть в кавычках ""
                // кавычки " внутри строк нужно предварить такой же кавычкой "
                if ($col_val && preg_match('/[",;\r\n]/', $col_val)) {
                    // поправим перенос строки
                    if ($row_delimiter === "\r\n") {
                        $col_val = str_replace("\r\n", '\n', $col_val);
                        $col_val = str_replace("\r", '', $col_val);
                    } else if ($row_delimiter === "\n") {
                        $col_val = str_replace("\n", '\r', $col_val);
                        $col_val = str_replace("\r\r", '\r', $col_val);
                    }

                    $col_val = str_replace('"', '""', $col_val); // предваряем "
                    $col_val = '"' . $col_val . '"'; // обрамляем в "
                }

                $cols[] = $col_val; // добавляем колонку в данные
            }

            $CSV_str .= implode($col_delimiter, $cols) . $row_delimiter; // добавляем строку в данные
        }

        $CSV_str = rtrim($CSV_str, $row_delimiter);

        // задаем кодировку windows-1251 для строки
        if ($file) {
            //            $CSV_str = iconv("UTF-8", "cp1251", $CSV_str);

            // создаем csv файл и записываем в него строку
            $done = file_put_contents($file, $CSV_str);

            return $done ? $CSV_str : false;
        }
        return $CSV_str;
    }
}
