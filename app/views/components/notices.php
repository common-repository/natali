<?php

if (isset($notices) && is_array($notices)) {
    foreach ($notices as $notice) {
        Natali_Template::the('components/notice', $notice);
    }
}
