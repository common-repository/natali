<?php
if (isset($data) && $data) {
    $data_attributes = '';

    foreach ($data as $key => $value) {
        $data_attributes .= "data-$key='$value' ";
    }
}

if (isset($style)) {
    $style = esc_attr($style);
}
?>

<button class="<?php echo isset($style) ? "nl-button_$style" : 'nl-button' ?> <?php echo esc_attr($className ?? null) ?>"
    <?php echo wp_kses_post($attributes ?? null) ?> id="<?php echo esc_attr($id ?? null) ?>"
        type="<?php echo esc_attr($type ?? null)?>" <?php echo wp_kses_post($data_attributes ?? null) ?>>
    <?php
    echo wp_kses_post($before_content ?? null);
    echo wp_kses_post($content ?? null);
    echo wp_kses_post($after_content ?? null); ?>
</button>
