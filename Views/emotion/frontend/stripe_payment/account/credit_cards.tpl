{extends file='parent:frontend/account/index.tpl'}

{* Main content *}
{block name='frontend_index_content'}
    <div id="center" class="grid_16 orders stripe_payment_credit_cards">
        <h1>
            {s name='credit_cards/title' namespace='frontend/plugins/stripe_payment/account'}{/s}
        </h1>

        {* Error messages *}
        {if $stripePaymentError}
            <div class="error">
                <h2>
                    {s name='credit_cards/error/title' namespace='frontend/plugins/stripe_payment/account'}{/s}
                </h2>
                {$stripePaymentError}
            </div>
        {/if}

        {if $creditCards|@count > 0}
            {* Credit card table *}
            <div class="table grid_16">
                {* Header *}
                <div class="table_head">
                    <div class="grid_3">
                        {s name='credit_cards/table/owner' namespace='frontend/plugins/stripe_payment/account'}{/s}
                    </div>
                    <div class="grid_3">
                        {s name='credit_cards/table/type' namespace='frontend/plugins/stripe_payment/account'}{/s}
                    </div>
                    <div class="grid_4">
                        {s name='credit_cards/table/number' namespace='frontend/plugins/stripe_payment/account'}{/s}
                    </div>
                    <div class="grid_2">
                        {s name='credit_cards/table/expiry_date' namespace='frontend/plugins/stripe_payment/account'}{/s}
                    </div>
                    <div class="grid_3 textright">
                        {s name='credit_cards/table/actions' namespace='frontend/plugins/stripe_payment/account'}{/s}
                    </div>
                </div>
                {* Body *}
                {foreach name=stripeCreditCards from=$creditCards item=creditCard}
                    <div class="table_row {if $smarty.foreach.stripeCreditCards.last}lastrow{/if}">
                        <div class="grid_3 bold">
                            {$creditCard.name}
                        </div>
                        <div class="grid_3">
                            {$creditCard.brand}
                        </div>
                        <div class="grid_4" style="margin-top: 15px;">
                            XXXXXXXXXXXX {$creditCard.last4}
                        </div>
                        <div class="grid_2">
                            {$creditCard.exp_month|string_format:"%02d"}/{$creditCard.exp_year}
                        </div>
                        <div class="grid_3 textright">
                            <strong>
                                <form name="stripeCreditCard-{$creditCard.id}" method="POST" action="{url controller='StripePaymentAccount' action='deleteCreditCard'}">
                                    <input type="hidden" name="cardId" value="{$creditCard.id}" />
                                    <button type="submit" class="button-middle small">{s name='credit_cards/table/actions/delete' namespace='frontend/plugins/stripe_payment/account'}{/s}</button>
                                </form>
                            </strong>
                        </div>
                    </div>
                {/foreach}
            </div>
        {else}
            {* No cards *}
            <div class="grid 16">
                <strong>
                    {s name='credit_cards/no_cards' namespace='frontend/plugins/stripe_payment/account'}{/s}
                </strong>
            </div>
        {/if}
    </div>
{/block}
