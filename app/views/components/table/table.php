<table class="wp-list-table widefat fixed striped table-view-list posts" style="margin: 16px 0;">
    <thead>
    <tr>
        <td id="cb" class="manage-column column-cb check-column">
            <label class="screen-reader-text" for="cb-select-all-1">
                <?php _e('Выделить все', 'wp-natali'); ?>
            </label>
            <input id="cb-select-all-1" type="checkbox">
        </td>
        <?php
        if (isset($head)) :
            foreach ($head as $key => $item) : ?>
                <td><?php echo wp_kses_post($item) ?></td>
            <?php endforeach;
        endif;
        ?>
    </tr>
    </thead>
    <tbody>
    <?php
    if (isset($list)) :
        foreach ($list as $key => $order) : ?>
            <tr class="iedit author-self level-0 type-product status-publish has-post-thumbnail hentry product_cat-16 pa_natali_composition-79 pa_natali_composition-32 pa_natali_labels-sale pa_natali_size-21 pa_natali_size-22 pa_natali_size-23 entry">
                <th scope="row" class="check-column">
                    <input id="cb-select-<?php echo esc_attr($order['id']) ?>" type="checkbox" name="orders"
                           value="<?php echo esc_attr($order['id']) ?>">
                    <div class="locked-indicator">
                        <span class="locked-indicator-icon" aria-hidden="true"></span>
                    </div>
                </th>
                <td><?php echo wp_kses_post($order['id']) ?></td>
                <td><?php echo wp_kses_post($order['client']) ?></td>
                <td><?php echo wp_kses_post($order['date']) ?></td>
                <td><?php echo wp_kses_post($order['total']) ?></td>
            </tr>
        <?php endforeach;
    endif;
    ?>
    </tbody>
</table>
