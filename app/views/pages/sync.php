<div class="natali natali-sync">
    <div class="natali-sync__wrapper">
        <h2 class="natali-sync__title"><?php _e('Данные о синхронизации товаров', 'wp-natali') ?></h2>
        <div style="margin: 32px 0;">
            <button
                    class="nl-button_primary"
                    :class="{'nl-button_loaded': loaded}"
                    @click="update()"
                    style="margin-right: 8px;"
                    :disabled="sync"
            >
                <?php _e('Проверить данные', 'wp-natali') ?>
            </button>
            <label style="margin-left: 16px;">
                <input type="checkbox" v-model="isRepeat">
                Не останавливаться при ошибке
            </label>
        </div>
        <div class="natali-sync__blocks">
            <div class="natali-block natali-block__published" :class="{loaded}">
                <div class="natali-block__label"><?php _e('Требуют импорта:', 'wp-natali') ?></div>
                <div class="natali-block__value">
                    {{counter.published ? counter.published : 'Нет данных' }}
                </div>
                <div class="natali-block__action">
                    <button
                            class="nl-button_primary"
                            :disabled="!counter.published || (stop && isImporting) || isUpdating || isDeleted"
                            @click="toggleImport()"
                    >
                        {{ isImporting ? 'Остановить' : 'Импорт' }}
                        <span v-if="isImporting" class="nl-loader"></span>
                    </button>
                </div>
            </div>
            <div class="natali-block natali-block__modify" :class="{loaded}">
                <div class="natali-block__label"><?php _e('Требуют изменения:', 'wp-natali') ?></div>
                <div class="natali-block__value">
                    {{counter.modify ? counter.modify : 'Нет данных' }}
                </div>
                <div class="natali-block__action">
                    <button
                            class="nl-button_primary"
                            :disabled="!counter.published || (stop && isUpdating) || isImporting || isDeleted"
                            @click="toggleUpdate()"
                    >
                        {{ isUpdating ? 'Остановить' : 'Обновить' }}
                        <span v-if="isUpdating" class="nl-loader"></span>
                    </button>
                </div>
            </div>
            <div class="natali-block natali-block__modify" :class="{loaded}">
                <div class="natali-block__label"><?php _e('Требуют удаления:', 'wp-natali') ?></div>
                <div class="natali-block__value">
                    {{counter.unpublished ? counter.unpublished : 'Нет данных' }}
                </div>
                <div class="natali-block__action">
                    <button
                            class="nl-button_primary"
                            :disabled="!counter.unpublished || (stop && isDeleted) || isImporting || isUpdating"
                            @click="toggleDelete()"
                    >
                        {{ isDeleted ? 'Остановить' : 'Удалить' }}
                        <span v-if="isDeleted" class="nl-loader"></span>
                    </button>
                </div>
            </div>
        </div>
        <br>
        <button
                class="nl-button_primary"
                :disabled="(stop && isRepairing) || isUpdating || isDeleted || isIimporting"
                style="margin-right: 8px;"
                @click="toggleRepair()"
        >
            {{ !isRepairing ? (page > 1) ? 'Продолжить исправление' : 'Исправить товары' : 'Остановить' }}
            <span v-if="isRepairing" class="nl-loader"></span>
        </button>
        <button
                class="nl-button_secondary"
                style="margin-right: 8px;"
                @click="resetRepair()"
        >
            Сбросить прогресс исправления
        </button>
        <div class="nl-statusbar" v-if="statusBar.enable">
            <div class="bar-title">Статус выполнения: {{statusBar.event}}</div>
            <div class="bar">
                <div class="progress" :style="statusBar.style"></div>
            </div>
        </div>
    </div>
    <div class="natali-sync__code-cron">
        <h2 class="natali-sync__title">
            <?php _e('Файл автоматической синхронизации', 'wp-natali') ?>
        </h2>
        <p>
            <?php _e('Создайте ежедневное задание на хостинге (cron задачу) указав путь к скрипту', 'wp-natali') ?>
            <code><?php echo NATALI_PLUGIN_DIR . '/natali_cron.php' ?></code>
        </p>
        <p>
            <?php _e('или скачайте данный файл если потребуется по', 'wp-natali') ?>
            <a href="<?php echo NATALI_PLUGIN_URL . '/natali_cron.php' ?>" download>
                <?php _e('ссылке', 'wp-natali') ?>
            </a>
        </p>
        <div class="natali-sync-notice warning">
            <b><?php _e('Внимание!', 'wp-natali') ?></b>
            <?php _e('Рекомендуем не устанавливать слишком частый вызов скрипта. Оптимальным значением будет 1-2 раза в день.
            Во время минимального посещения сайта. Но и меньше 1 раза в день так же не желательно устанавливать выполнение скрипта. 
            Так как на сайте натали каждый день происходит достаточно много изменений, ии данные быстро теряют актуальность.',
                     'wp-natali') ?>
        </div>
        <h3><?php _e('Содержимое данного файла:', 'wp-natali') ?></h3>
        <p>
            <?php
            $file_path = NATALI_PLUGIN_DIR . '/natali_cron.php';

            if (file_exists($file_path)) {
                $file_cron = _sanitize_text_fields(file_get_contents($file_path), true);

                echo '<pre class="natali-code">', $file_cron, '</pre>';
            } ?>
        </p>
        <h3><?php _e('Остались вопросы?', 'wp-natali') ?></h3>
        <p>
            <?php _e('Посмотрите инструкцию для вашего хостинга', 'wp-natali') ?>
        <ul>
            <li><a href="https://beget.com/ru/kb/manual/crontab" target="_blank">Beget</a></li>
            <li><a href="https://www.nic.ru/help/planirovshik-cron-zapusk-programm-po-raspisaniyu_6791.html"
                   target="_blank">
                    RU Center
                    (nic.ru)</a></li>
            <li><a href="https://timeweb.com/ru/help/pages/viewpage.action?pageId=4358482" target="_blank">Timeweb</a>
            </li>
            <li><a href="https://hyperhost.ua/info/ru/osobennosti-rabotyi-cron-na-hostinge"
                   target="_blank">Hyperhost</a></li>
            <li>
                <a href="https://help.reg.ru/hc/ru/articles/4408054641425-%D0%9D%D0%B0%D1%81%D1%82%D1%80%D0%BE%D0%B9%D0%BA%D0%B0-cron-%D0%B7%D0%B0%D0%B4%D0%B0%D0%BD%D0%B8%D1%8F-%D0%BD%D0%B0-%D0%B2%D1%8B%D0%B4%D0%B5%D0%BB%D0%B5%D0%BD%D0%BD%D0%BE%D0%BC-%D1%81%D0%B5%D1%80%D0%B2%D0%B5%D1%80%D0%B5"
                   target="_blank">
                    Reg.ru
                </a>
            </li>
        </ul>
        </p>
    </div>
</div>