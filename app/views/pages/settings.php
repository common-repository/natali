<?php
$select = 'selected="selected"';
?>
<div class="natali-product__settings nl-setting">
    <form class="nl-setting-form" @submit="saveSettings">
        <table class="wp-list-table">
            <tbody>
            <tr>
                <td colspan="2">
                    <h3><?php _e('Ключи API Woocommerce', 'wp-natali') ?></h3>
                    <p><?php _e('Обязательно выдайте права плагину на ', 'wp-natali') ?>
                        <code><?php _e('чтение/запись', 'wp-natali') ?></code>
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('Пользовательский ключ', 'wp-natali') ?>
                </td>
                <td>
                    <input type="password" name="user_key"
                           value="<?php echo esc_attr($settings['user_key'] ?? null); ?>">
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('Секретный код пользователя', 'wp-natali') ?>
                </td>
                <td>
                    <input type="password" name="secret_key"
                           value="<?php echo esc_attr($settings['secret_key'] ?? null) ?>">
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <hr>
                </td>
            </tr>
            <tr>
                <td colspan="2"><h3><?php _e('Цена', 'wp-natali') ?></h3></td>
            </tr>
            <tr>
                <td>
                    <?php _e('Тип цены', 'wp-natali') ?>
                </td>
                <td>
                    <select name="price_type" id="price_type">
                        <option value="price"
                            <?php echo $settings['price_type'] === 'price' ? 'selected' : null ?>>
                            Розница
                        </option>
                        <option value="priceSmallWholesale"
                            <?php echo $settings['price_type'] === 'priceSmallWholesale' ? 'selected' : null ?>>
                            Мелкий опт
                        </option>
                        <option value="priceWholesale"
                            <?php echo $settings['price_type'] === 'priceWholesale' ? 'selected' : null ?>>
                            Опт
                        </option>
                    </select>
                    <span><?php _e(' - Выберите от какой цены назначить наценку') ?></span>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('Процент наценки', 'wp-natali') ?>
                </td>
                <td class="natali-field">
                    <input type="text" name="regular_price_margin"
                           value="<?php echo esc_attr($settings['regular_price_margin'] ?? null) ?>"
                           style="max-width: 90px">
                    <div class="natali-input-append">%</div>
                    <span><?php _e(' - Назначает наценку на выбранную цену') ?></span>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('Процент цены со скидкой', 'wp-natali') ?>
                </td>
                <td class="natali-field">
                    <input type="text" name="sale_price_margin"
                           value="<?php echo esc_attr($settings['sale_price_margin'] ?? null) ?>"
                           style="max-width: 90px">
                    <div class="natali-input-append">%</div>
                    <span><?php _e(' - Назначает скидку от суммы с наценкой') ?></span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <hr>
                </td>
            </tr>
            <tr>
                <td colspan="2"><h3><?php _e('Товар', 'wp-natali') ?></h3></td>
            </tr>
            <tr>
                <td>
                    <?php _e('Тип создаваемого товара', 'wp-natali') ?>
                </td>
                <td>
                    <select name="product_type" id="product_type">
                        <option value="simple" <?php echo $settings['product_type'] === 'simple' ? 'selected' : null ?>>
                            <?php _e('Простой товар', 'wp-natali') ?>
                        </option>
                        <option value="variable" <?php echo $settings['product_type'] === 'variable' ? 'selected' : null ?>>
                            <?php _e('Вариативный товар', 'wp-natali') ?>
                        </option>
                    </select>
                    <span><?php _e(' - Простой товар, более быстрый импорт (Без возможности экспорта)') ?></span>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('Качество фотографий', 'wp-natali') ?>
                </td>
                <td>
                    <select name="images" id="images">
                        <option value="previewUrl" <?php echo $settings['images'] === 'previewUrl' ? 'selected' :
                            null ?>>
                            Сжатые фотографии
                        </option>
                        <option value="url" <?php echo $settings['images'] === 'url' ? 'selected' : null ?>>
                            Высокое качество
                        </option>
                    </select>
                    <span><?php _e(' - Рекомендуем (Сжатые фотографии)') ?></span>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('Одно изображение для цвета', 'wp-natali') ?>
                </td>
                <td>
                    <select name="image_color">
                        <option value="0" <?php echo esc_attr($settings['image_color'] === '0' ? 'selected' : null) ?>>
                            <?php _e('Нет', 'wp-natali') ?>
                        </option>
                        <option value="1" <?php echo esc_attr($settings['image_color'] === '1' ? 'selected' : null) ?>>
                            <?php _e('Да', 'wp-natali') ?>
                        </option>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <hr>
                </td>
            </tr>
            <tr>
                <td colspan="2"><h3><?php _e('Атрибуты товаров', 'wp-natali') ?></h3></td>
            </tr>
            <tr>
                <td>
                    <?php _e('Отображать блок "состав и материал"', 'wp-natali') ?>
                </td>
                <td>
                    <select name="attributes_display">
                        <option value="0" <?php echo esc_attr($settings['attributes_display'] == 0 ? 'selected' : null) ?>>
                            <?php _e('Нет', 'wp-natali') ?>
                        </option>
                        <option value="1" <?php echo esc_attr($settings['attributes_display'] == 1 ? 'selected' : null) ?>>
                            <?php _e('Да', 'wp-natali') ?>
                        </option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('Куда вывести атрибуты', 'wp-natali') ?>
                </td>
                <td>
                    <select name="attributes_place">
                        <option value="woocommerce_after_single_product_summary"
                            <?php echo esc_attr($settings['attributes_place'] === 'woocommerce_after_single_product_summary' ? 'selected': null) ?>
                        >
                            <?php _e('Перед вкладками', 'wp-natali') ?>
                        </option>
                        <option value="woocommerce_before_add_to_cart_form"
                            <?php echo esc_attr($settings['attributes_place'] === 'woocommerce_before_add_to_cart_form' ? 'selected': null) ?>
                        >
                            <?php _e('После краткого описания', 'wp-natali') ?>
                        </option>
                        <option value="woocommerce_product_additional_information"
                            <?php echo esc_attr($settings['attributes_place'] === 'woocommerce_product_additional_information' ? 'selected': null) ?>
                        >
                            <?php _e('Во вкладке детали', 'wp-natali') ?>
                        </option>
                        <option value="woocommerce_product_description_heading"
                            <?php echo esc_attr($settings['attributes_place'] === 'woocommerce_product_description_heading' ? 'selected': null) ?>
                        >
                            <?php _e('Во вкладке описания', 'wp-natali') ?>
                        </option>
                        <option value="woocommerce_product_after_tabs"
                            <?php echo esc_attr($settings['attributes_place'] === 'woocommerce_product_after_tabs' ? 'selected': null) ?>
                        >
                            <?php _e('После вкладок', 'wp-natali') ?>
                        </option>
                        <option value="woocommerce_show_product_thumbnails"
                            <?php echo esc_attr($settings['attributes_place'] === 'woocommerce_show_product_thumbnails' ? 'selected': null) ?>
                        >
                            <?php _e('Под изображением', 'wp-natali') ?>
                        </option>
                        <option value="woocommerce_after_add_to_cart_button"
                            <?php echo esc_attr($settings['attributes_place'] === 'woocommerce_after_add_to_cart_button' ? 'selected': null) ?>
                        >
                            <?php _e('После кнопки в корзину', 'wp-natali') ?>
                        </option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('Приоритет вывода', 'wp-natali') ?>
                </td>
                <td class="attributes_priority">
                    <input type="text" name="attributes_priority"
                           value="<?php echo esc_attr($settings['attributes_priority'] ?? 10) ?>" style="max-width: 90px">
                    <span><?php _e(' - Чем больше число тем позднее будет вывод блока') ?></span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <hr>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('Лейбол', 'wp-natali') ?>
                </td>
                <td>
                    <select name="labels">
                        <option value="N" <?php echo esc_attr($settings['labels'] === 'N' ? 'selected' : null) ?>>
                            <?php _e('Нет', 'wp-natali') ?>
                        </option>
                        <option value="Y" <?php echo esc_attr($settings['labels'] === 'Y' ? 'selected' : null) ?>>
                            <?php _e('Да', 'wp-natali') ?>
                        </option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('Состав', 'wp-natali') ?>
                </td>
                <td>
                    <select name="composition">
                        <option value="N" <?php echo esc_attr($settings['composition'] === 'N' ? 'selected' : null) ?>>
                            <?php echo __('Нет', 'wp-natali') ?>
                        </option>
                        <option value="Y" <?php echo esc_attr($settings['composition'] === 'Y' ? 'selected' : null) ?>>
                            <?php _e('Да', 'wp-natali') ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('Брэнд', 'wp-natali') ?>
                </td>
                <td>
                    <select name="natali_brands">
                        <option value="N" <?php echo esc_attr($settings['natali_brands'] === 'N' ? 'selected': null) ?>>
                            <?php echo __('Нет', 'wp-natali') ?>
                        </option>
                        <option value="Y" <?php echo esc_attr($settings['natali_brands'] === 'Y' ? 'selected': null) ?>>
                            <?php _e('Да', 'wp-natali') ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('Материалы', 'wp-natali') ?>
                </td>
                <td>
                    <select name="materials">
                        <option value="N" <?php echo esc_attr($settings['materials'] === 'N' ? 'selected' : null) ?>>
                            <?php _e('Нет', 'wp-natali') ?>
                        </option>
                        <option value="Y" <?php echo esc_attr($settings['materials'] === 'Y' ? 'selected' : null) ?>>
                            <?php _e('Да', 'wp-natali') ?>
                        </option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('Видео', 'wp-natali') ?>
                </td>
                <td>
                    <select name="video">
                        <option value="N" <?php echo esc_attr($settings['video'] === 'N' ? 'selected': null) ?>>
                            <?php _e('Нет', 'wp-natali') ?>
                        </option>
                        <option value="Y" <?php echo esc_attr($settings['video'] === 'Y' ? 'selected': null) ?>>
                            <?php _e('Да', 'wp-natali') ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('Размер мин', 'wp-natali') ?>
                </td>
                <td>
                    <select name="minSize">
                        <option value="N" <?php echo esc_attr($settings['minSize'] === 'N' ? 'selected': null) ?>>
                            <?php _e('Нет', 'wp-natali') ?>
                        </option>
                        <option value="Y" <?php echo esc_attr($settings['minSize'] === 'Y' ? 'selected': null) ?>>
                            <?php _e('Да', 'wp-natali') ?>
                        </option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('Размер макс', 'wp-natali') ?>
                </td>
                <td>
                    <select name="maxSize">
                        <option value="N" <?php echo esc_attr($settings['maxSize'] === 'N' ? 'selected': null) ?>>
                            <?php _e('Нет', 'wp-natali') ?>
                        </option>
                        <option value="Y" <?php echo esc_attr($settings['maxSize'] === 'Y' ? 'selected': null) ?>>
                            <?php _e('Да', 'wp-natali') ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('Маркированный товар', 'wp-natali') ?>
                </td>
                <td>
                    <select name="isMarked">
                        <option value="N" <?php echo esc_attr($settings['isMarked'] === 'N' ? 'selected': null) ?>>
                            <?php _e('Нет', 'wp-natali') ?>
                        </option>
                        <option value="Y" <?php echo esc_attr($settings['isMarked'] === 'Y' ? 'selected': null) ?>>
                            <?php _e('Да', 'wp-natali') ?>
                        </option>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <hr>
                </td>
            </tr>
            <tr>
                <td colspan="2"><h3><?php _e('Таблица размеров', 'wp-natali') ?></h3></td>
            </tr>
            <tr>
                <td>
                    <?php _e('Отображать таблицу', 'wp-natali') ?>
                </td>
                <td>
                    <select name="garments_display">
                        <option value="0" <?php echo esc_attr($settings['garments_display'] === '0' ? 'selected': null) ?>>
                            <?php _e('Нет', 'wp-natali') ?>
                        </option>
                        <option value="1" <?php echo esc_attr($settings['garments_display'] === '1' ? 'selected': null) ?>>
                            <?php _e('Да', 'wp-natali') ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('Куда вывести таблицу', 'wp-natali') ?>
                </td>
                <td>
                    <select name="garments_place">
                        <option value="woocommerce_after_single_product_summary"
                            <?php echo esc_attr($settings['garments_place'] === 'woocommerce_after_single_product_summary' ? 'selected': null) ?>
                        >
                            <?php _e('Перед вкладками', 'wp-natali') ?>
                        </option>
                        <option value="woocommerce_before_add_to_cart_form"
                            <?php echo esc_attr($settings['garments_place'] === 'woocommerce_before_add_to_cart_form' ? 'selected': null) ?>
                        >
                            <?php _e('После краткого описания', 'wp-natali') ?>
                        </option>
                        <option value="woocommerce_product_additional_information"
                            <?php echo esc_attr($settings['garments_place'] === 'woocommerce_product_additional_information' ? 'selected': null) ?>
                        >
                            <?php _e('Во вкладке детали', 'wp-natali') ?>
                        </option>
                        <option value="woocommerce_product_description_heading"
                            <?php echo esc_attr($settings['garments_place'] === 'woocommerce_product_description_heading' ? 'selected': null) ?>
                        >
                            <?php _e('Во вкладке описания', 'wp-natali') ?>
                        </option>
                        <option value="woocommerce_product_after_tabs"
                            <?php echo esc_attr($settings['garments_place'] === 'woocommerce_product_after_tabs' ? 'selected': null) ?>
                        >
                            <?php _e('После вкладок', 'wp-natali') ?>
                        </option>
                        <option value="woocommerce_show_product_thumbnails"
                            <?php echo esc_attr($settings['garments_place'] === 'woocommerce_show_product_thumbnails' ? 'selected': null) ?>
                        >
                            <?php _e('Под изображением', 'wp-natali') ?>
                        </option>
                        <option value="woocommerce_after_add_to_cart_button"
                            <?php echo esc_attr($settings['garments_place'] === 'woocommerce_after_add_to_cart_button' ? 'selected': null) ?>
                        >
                            <?php _e('После кнопки в корзину', 'wp-natali') ?>
                        </option>
                        <option value="create_tab"
                            <?php echo esc_attr($settings['garments_place'] === 'create_tab' ? 'selected': null) ?>
                        >
                            <?php _e('Создать отдельную вкладку', 'wp-natali') ?>
                        </option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('Приоритет вывода', 'wp-natali') ?>
                </td>
                <td class="garments_priority">
                    <input type="text" name="garments_priority"
                           value="<?php echo esc_attr($settings['garments_priority'] ?? 10) ?>" style="max-width: 90px">
                    <span><?php _e(' - Чем больше число тем позднее будет вывод блока') ?></span>
                </td>
            </tr>
            </tbody>
        </table>
        <br>

        <div class="nl-setting-form__footer">
            <button class="nl-button_primary" type="submit" :class="{'nl-button_loaded' : saving}">{{ btnLabel }}</button>
        </div>
    </form>
</div>