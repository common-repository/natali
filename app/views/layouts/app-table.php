<div class="wrap">
    <div class="natali-product <?php echo esc_attr($_GET['page'] ?? null) ?>">
        <div class="natali-product__wrapper">
            <?php Natali_Template::the('layouts/header', ['page_title' => $page_title ?? __('Натали', 'wp-natali')]); ?>
            <div class="natali-notices">
                <?php
                if (isset($notices) && $notices) {
                    Natali_Template::the('components/notices', ['notices' => $notices]);
                }
                ?>
            </div>
            <?php if (isset($list) && $list) : ?>
                <form style="margin: 0 0 32px 0;" class="<?php echo esc_attr($classForm ?? null) ?>" id="<?php echo esc_attr($idForm ?? null) ?>">
                    <?php Natali_Template::the('components/table/table', [
                        'head' => $head ?? null,
                        'list' => $list ?? null
                    ]); ?>
                    <button type="submit" class="nl-button nl-button_primary" id="export_start" disabled>
                        <?php _e('Экспорт', 'wp-natali') ?>
                    </button>
                </form>
            <?php else : ?>
                <div class="natali-export__body empty" style="text-align: center;">
                    <h2><?php _e('Нету активных заказов', 'wp-natali') ?></h2>
                </div>
            <?php endif; ?>

            <?php Natali_Template::the('layouts/footer'); ?>
        </div>
    </div>
</div>

