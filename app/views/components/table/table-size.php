<?php
if (isset($rows)): ?>
    <div class="natali-place-size-tables">
        <h4 class="natali-table-size__title"><?php _e('Таблица размеров', 'wp-natali') ?></h4>

        <?php foreach ($rows as $key => $row): ?>
            <table class="natali-table-size" id="natali-table-size_<?php echo esc_attr($key) ?>">
                <tbody>
                <?php if (esc_attr($row['title']) !== '-'): ?>
                    <tr>
                        <td colspan="<?php echo (count($row['dimensions']) + 1) ?>"><?php echo esc_attr($row['title']) ?></td>
                    </tr>
                <?php endif; ?>
                <tr class="natali-table-size__row">
                    <th class="natali-table-size__td-title"><?php _e('Размеры', 'wp-natali') ?></th>
                    <?php foreach ($row['dimensions'] as $dimension): ?>
                        <td><?php echo esc_attr($dimension['size']) ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr class="natali-table-size__row">
                    <th class="natali-table-size__td-title"><?php echo esc_attr($dimension['item'][0]['title'] ?? null) ?></th>
                    <?php foreach ($row['dimensions'] as $dimension): ?>
                        <td><?php echo $dimension['item'][0]['value'] ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr class="natali-table-size__row">
                    <th class="natali-table-size__td-title"><?php echo esc_attr($dimension['item'][1]['title'] ?? null) ?></th>
                    <?php foreach ($row['dimensions'] as $dimension): ?>
                        <td><?php echo $dimension['item'][1]['value'] ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr class="natali-table-size__row">
                    <th class="natali-table-size__td-title"><?php echo esc_attr($dimension['item'][2]['title'] ?? null) ?></th>
                    <?php foreach ($row['dimensions'] as $dimension): ?>
                        <td><?php echo $dimension['item'][2]['value'] ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr class="natali-table-size__row">
                    <th class="natali-table-size__td-title"><?php echo esc_attr($dimension['item'][3]['title'] ?? null) ?></th>
                    <?php foreach ($row['dimensions'] as $dimension): ?>
                        <td><?php echo $dimension['item'][3]['value'] ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr class="natali-table-size__row">
                    <th class="natali-table-size__td-title"><?php echo $dimension['item'][4]['title'] ?></th>
                    <?php foreach ($row['dimensions'] as $dimension): ?>
                        <td><?php echo $dimension['item'][4]['value'] ?></td>
                    <?php endforeach; ?>
                </tr>
                </tbody>
            </table>
        <?php endforeach; ?>
        <small class="natali-table-size__small"><?php _e('Замеры и вес изделий могут незначительно отличаться',
                                                         'wp-natali') ?></small>
    </div>
<?php endif; ?>