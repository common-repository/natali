<?php
/**
 * @var $settings Natali_Model_Settings
 */

if ($settings->get('attributes_display') == 1) :?>
<div class="natali-field">
    <!--    --><?php //if ($settings->get('labels') === 'Y' && isset($data['labels']) && $data['labels']): ?>
    <!--        <span class="natali-field__label">--><?php //_e('Лебл: ', 'wp-natali') ?><!--</span>-->
    <!--        <span class="natali-field__value">-->
    <?php //echo esc_html(implode(',', $data['labels'])) ?><!--</span><br>-->
    <!--    --><?php //endif; ?>
    <?php if ($settings->get('materials') === 'Y' && isset($data['materials']) && $data['materials'] && is_array($data['materials'])): ?>
        <span class="natali-field__label">
            <?php _e('Материал: ', 'wp-natali'); ?>
        </span>
        <span class="natali-field__value">
            <?php
            $total = count($data['materials']);
            $counter = 0;
            ?>
            <?php foreach ($data['materials'] as $material): ?>
                <?php $counter++;
                if ($counter == $total) {
                    echo esc_html($material['title'] . ' ');
                } else {
                    echo esc_html($material['title'] . ', ');
                } ?>
            <?php endforeach; ?>
        </span><br>
    <?php endif; ?>
    <?php if ($settings->get('composition') === 'Y' && isset($data['composition']) && $data['composition']): ?>
        <span class="natali-field__label"><?php _e('Состав:', 'wp-natali') ?></span>
        <span class="natali-field__value"><?php echo esc_html($data['composition'] ?? null) ?></span><br>
    <?php endif; ?>
    <!--    --><?php //if (isset($data['minSize']) && isset($data['maxSize']) && $data['minSize'] && $data['maxSize']): ?>
    <!--        <span class="natali-field__label">--><?php //_e('Размеры: ', 'wp-natali') ?><!--</span>-->
    <!--        <span class="natali-field__value">-->
    <?php //echo esc_html($data['minSize']) . ' - ' . $data['maxSize'] ?><!--</span><br>-->
    <!--    --><?php //endif; ?>
</div>
<?php endif; ?>