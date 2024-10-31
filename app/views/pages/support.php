<?php
defined('ABSPATH') || exit;
?>
<div class="natali-product__support natali-support" id="natali-support">
    <div class="natali-support__wrapper">
        <div class="natali-support__left">
            <div class="natali-support__title">
                <h2><?php _e('Содержимое журнала', 'wp-natali') ?></h2>
            </div>
            <p>
                <?php _e('Здесь вы можете посмотреть все действия, которые проводил плагин за время своей работы. 
                А так же все ошибки и предупреждения', 'wp-natali') ?>
            </p>
            <div class="natali-log-place">
                <?php echo $log_file['body'] ?? '<span class="natali-empty-place">Здесь появятся записи действий плагина</span>' ?>
            </div>
            <?php if (isset($log_file['body'])): ?>
                <p>
                    <a href="<?php echo esc_attr($log_file['url']) ?>" class="nl-button nl-button_secondary" download>
                        <?php _e('Скачать файл журнала', 'wp-natali') ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>
        <div class="natali-support__right">
            <div class="natali-support__title">
                <h2><?php _e('Связаться тех. поддержкой', 'wp-natali') ?></h2>
            </div>
            <p>
                <?php _e('Заполните данную форму. что бы связаться с тех. поддержкой. 
                Ответ придет в течении 1-2 рабочих дней на почту указанную в форме. Файл журнала плагина будет автоматически отправлен в письме',
                         'wp-natali') ?>
            </p>
            <div>
                <div v-if="adBlock" style="margin: 16px 0">
                    <div class="natali-sync-notice warning">
                        <b>Внимание!</b> отключите блокировщик рекламы для этой страницы, что бы отправить письмо в
                        службу поддержки. Иначе возможна ошибка при отправке письма
                    </div>
                </div>
                <form ref="form" class="natali-form-support" id="natali-form-support"
                      data-logfile="<?php echo $log_file['url'] ?>">
                    <input type="text" placeholder="Ваше имя" name="name" required>
                    <input type="email" placeholder="Ваш e-mail" name="email" required>
                    <textarea id="" cols="30" rows="10" name="message" placeholder="Ваше сообщение" required></textarea>
                    <button class="nl-button nl-button_primary"
                            type="submit"
                            :class="{'nl-button_loaded' : loaded}"
                    >
                        {{ label }}
                    </button>
                    <small>
                        <?php _e('* Файл журнала, будет автоматически прикреплен к письму', 'wp-natali') ?>
                    </small>
                </form>
                <div v-if="isError" style="margin: 16px 0">
                    <div class="natali-sync-notice natali-sync-notice_error mv-16">
                        Ошибка отправки формы <code>{{log}}</code>
                    </div>
                </div>
                <div v-if="isSend">
                    <div class="natali-sync-notice natali-sync-notice_succes mv-16">
                        Сообщение успешно отправлено!
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>