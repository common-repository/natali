<?php
defined('ABSPATH') || exit;
?>
<div class="nl-import <?php echo !$disable ?: 'disable' ?>">
    <div class="nl-import__wrapper">
        <?php
        if ($categories): ?>
        <form class="nl-categories nl-categories-form js-stickybit-parent" @submit="saveSettings($event)">
            <div class="nl-categories__wrapper" style="display: grid; grid-Natali_Template-columns: 1fr">
                <div class="nl-categories__item">
                    <div class="nl-categories__id">
                        <b><?php _e('ID', 'wp-natali') ?></b>
                    </div>
                    <div class="nl-categories__label">
                        <b><?php _e('Название категории', 'wp-natali') ?></b>
                    </div>
                    <div class="nl-categories__list">
                        <b><?php _e('Выбрать категорию на сайте', 'wp-natali') ?></b>
                    </div>
                </div>
                <?php
                foreach ($categories as $key => $cat) {
                    switch ($cat['DEPTH']) {
                        case 0:
                            $depthMargin = '';
                            $cat['title'] = '<b>' . $cat['title'] . '</b>';
                            $border = null;
                            break;
                        case 1:
                            $depthMargin = str_repeat('- ', 1);
                            $border = 'style="border-left: 1px solid rgba(0,0,0, .5);"';
                            break;
                        case 2:
                            $depthMargin = str_repeat('- ', 1);
                            $border = 'style="border-left: 1px solid rgba(0,0,0, .5); margin-left: 24px"';
                            break;
                        case 3:
                            $depthMargin = str_repeat('- ', 1);
                            $border = 'style="border-left: 1px solid rgba(0,0,0, .5)"';
                            break;
                        case 4:
                            $depthMargin = str_repeat('- ', 1);
                            break;
                        default:
                            $depthMargin = '';
                    }

                    $sectionString = $depthMargin . $cat['title'];
                    ?>
                    <div class="nl-categories__item">
                        <div class="nl-categories__id">
                            <?php echo wp_kses_post($cat['categoryId']) ?>
                        </div>
                        <div class="nl-categories__label" <?php echo wp_kses_post($border ?? null) ?>>
                            <?php echo wp_kses_post($sectionString) ?>
                        </div>
                        <div class="nl-categories__list">
                            <?php
                            $args = [
                                'show_option_all'   => __('Не импортировать', 'wp-natali'),
                                'show_option_none'  => '',
                                'option_none_value' => -1,
                                'orderby'           => 'ID',
                                'order'             => 'ASC',
                                'show_last_update'  => 0,
                                'show_count'        => 0,
                                'hide_empty'        => 0,
                                'child_of'          => 0,
                                'exclude'           => '',
                                'echo'              => 1,
                                'selected'          => $settings[$key]['wc_id'] ?? 0,
                                'hierarchical'      => 1,
                                'name'              => 'SELECTED_SECTIONS[' . $cat['categoryId'] . ']',
                                'id'                => 'name' . $cat['categoryId'],
                                'class'             => 'postform js-select2',
                                'depth'             => 0,
                                'tab_index'         => 0,
                                'taxonomy'          => 'product_cat',
                                'hide_if_empty'     => false,
                                'value_field'       => 'term_id', // значение value e option
                                'required'          => false,
                            ];

                            wp_dropdown_categories($args); ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <div class="nl-categories__footer" :class="{'nl-loading' : loading}">
                <button class="nl-button_primary" id="natali_import_submit" type="submit" :disabled="isImporting">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 8px;">
                        <path d="M20.9367 6.87422L17.1258 3.06328C16.95 2.8875 16.7344 2.75859 16.5 2.68828V2.625H3.375C2.96016 2.625 2.625 2.96016 2.625 3.375V20.625C2.625 21.0398 2.96016 21.375 3.375 21.375H20.625C21.0398 21.375 21.375 21.0398 21.375 20.625V7.93359C21.375 7.53516 21.218 7.15547 20.9367 6.87422ZM9 4.3125H15V6.75H9V4.3125ZM19.6875 19.6875H4.3125V4.3125H7.5V7.5C7.5 7.91484 7.83516 8.25 8.25 8.25H15.75C16.1648 8.25 16.5 7.91484 16.5 7.5V4.82344L19.6875 8.01094V19.6875ZM12 10.3594C10.1367 10.3594 8.625 11.8711 8.625 13.7344C8.625 15.5977 10.1367 17.1094 12 17.1094C13.8633 17.1094 15.375 15.5977 15.375 13.7344C15.375 11.8711 13.8633 10.3594 12 10.3594ZM12 15.6094C10.9641 15.6094 10.125 14.7703 10.125 13.7344C10.125 12.6984 10.9641 11.8594 12 11.8594C13.0359 11.8594 13.875 12.6984 13.875 13.7344C13.875 14.7703 13.0359 15.6094 12 15.6094Z" fill="white"/>
                    </svg>
                    <?php _e('Сохранить', 'wp-natali') ?>
                </button>
                <button class="nl-button_primary" @click="toggleImport" type="button" :disabled="!haveImport || (stop && isImporting)">
                    {{ isImporting ? 'Остановить' : 'Импорт' }}
                    <span v-if="isImporting" class="nl-loader"></span>
                </button>
                <div class="nl_import_bar">
                    <div class="nl_import_bar__counter">
                        <div>
                            <span><?php _e('Перезапускать импорт при ошибке ') ?></span>
                            <span class="nl_import_bar__finish"><input type="checkbox" v-model="isRepeat"></span>
                        </div>
                        <div>
                            <span><?php _e('Завершено:') ?></span>
                            <span class="nl_import_bar__finish"> {{ imported }}</span>
                        </div>
                        <div>
                            <span><?php _e('В очереди:') ?></span>
                            <span class="nl_import_bar__total"> {{ haveImport }}</span>
                        </div>
                        <div>
                            <span><?php _e('Расчетное время:') ?></span>
                            <span class="nl_import_bar__time">{{ time }}</span>
                        </div>
                        <div>
                            <span><?php _e('Всего импортировано: ') ?></span>
                            <span class="nl_import_bar__time">{{ quantity }}</span>
                        </div>
                    </div>
                    <div class="nl_import_bar__statusbar">
                        <div class="nl_import_bar__progress"></div>
                    </div>
                </div>
            </div>
        </form>
        <?php else : ?>
        <div class="natali-import-error">
            <h2 class="natali-lost-error"><?php _e('Не могу получить доступ к natali37.ru', 'wp-natali') ?></h2>
            <p>Проверьте доступность сайта natali37.ru</p>
        </div>
        <?php endif; ?>
    </div>
</div>