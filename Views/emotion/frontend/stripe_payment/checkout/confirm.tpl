{block name="frontend_index_content_top" append}
    {if $stripePaymentError}
        <div class="grid_20 first">
            <div class="error">
                <div class="center">{s namespace=frontend/plugins/payment/stripe_payment/base name=payment_error/title}{/s}</div>
                <br />
                <div class="normal" style="text-align: left; font-weight: normal;">{$stripePaymentError}</div>
            </div>
        </div>
    {/if}
{/block}
