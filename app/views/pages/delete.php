<?php
defined('ABSPATH') || exit;
?>
<div class="natali-product__delete natali-delete">
    <div v-if="message && !next" style="margin: 16px 0">
        <div class="natali-sync-notice warning" >
            {{ message }}
        </div>
    </div>
    <table style="margin-bottom: 32px;">
        <tbody>
            <tr>
                <td><span>Всего товаров:</span></td>
                <td><span>{{products}}</span></td>
            </tr>
            <tr>
                <td><span>Удалять по:</span></td>
                <td><input type="number" v-model="step" min="1" max="20"></td>
                <td><span>от 1 до 20</span></td>
            </tr>
        </tbody>
    </table>
    <div>
        <button  class="nl-button nl-button_primary" id="nl_button_delete" @click="stopAndStart" :disabled="loadingData">
            {{ buttonLabel }}
            <span v-if="deleting" class="nl-loader nl-loader_trans nl-loader_white" style="margin-left: 4px;"></span>
        </button>
    </div>
</div>