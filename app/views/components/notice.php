<div class="notice notice-<?php echo esc_attr($type ?? null) ?>
<?php echo esc_attr($class ?? null) ?>"
     id="<?php echo esc_attr($id ?? null) ?>">
    <p class="notice__wrapper">
        <?php echo wp_kses_post($content ?? null) ?>
    </p>
</div>