<div id="<?php echo esc_attr($id ?? null) ?>" class="panel woocommerce_options_panel natali-tab-admin">
    <div>
        <?php if (isset($data['garments'])) {
            Natali_Template::the('components/table/table-size', [
                    'rows' => $data['garments']
            ]);
        } ?>
    </div>
</div>