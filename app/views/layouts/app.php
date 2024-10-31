<?php
defined('ABSPATH') || exit;
?>
<div class="wrap" id="natali-app">
    <div class="natali-product <?php echo esc_attr($page ?? null) ?>" id="<?php echo esc_attr($page ?? null) ?>">
        <div class="natali-product__wrapper">
            <?php
            Natali_Template::the('layouts/header', [
                'page_title'       => $page_title ?? __('Натали', 'wp-natali'),
                'page_description' => $page_description ?? null
            ]);

            if (isset($notices) && $notices) {
                Natali_Template::the('components/notices', ['notices' => $notices]);
            }
            ?>
            <div class="notice notice-error" v-if="error">
                <p><b>{{error.event}}</b> {{error.message}}</p>
            </div>
            <div class="notice notice-success" v-if="success">
                <p><b>{{success.event}}</b> {{success.message}}</p>
            </div>
            <div class="natali-product__body postbox">
                <div class="inside">
                    <?php if (isset($template)) : ?>
                        <?php Natali_Template::the(esc_attr($template), $content ?? null); ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php Natali_Template::the('layouts/footer'); ?>
        </div>
    </div>
</div>