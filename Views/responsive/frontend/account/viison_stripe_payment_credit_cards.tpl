{extends file="frontend/account/index.tpl"}

{namespace name='frontend/plugins/viison_stripe/account'}

{* Breadcrumb *}
{block name="frontend_index_start" append}
	{$sBreadcrumb[] = ["name" => "{s name='credit_cards/title'}{/s}", "link" => {url}]}
	{$sActiveAction = 'manageCreditCards'}
{/block}

{* Main content *}
{block name="frontend_index_content"}
	<div class="account--viison-stripe-payment account--content register--content" data-register="true">
		{* Headline *}
		<div class="account--welcome">
			<h1 class="panel--title">{s name="credit_cards/title"}{/s}</h1>
		</div>

		{if $creditCards|@count > 0}
			{* Credit card table *}
			<div class="panel has--border is--rounded">
				<div class="panel--body is--rounded card-table">
					{* Header *}
					<div class="table--header block-group">
						<div class="panel--th block">{s name="credit_cards/table/owner"}{/s}</div>
						<div class="panel--th block">{s name="credit_cards/table/type"}{/s}</div>
						<div class="panel--th block">{s name="credit_cards/table/number"}{/s}</div>
						<div class="panel--th block">{s name="credit_cards/table/expiry_date"}{/s}</div>
						<div class="panel--th block">{s name="credit_cards/table/actions"}{/s}</div>
					</div>

					{* Rows *}
					{foreach name=stripeCreditCards from=$creditCards item=creditCard}
						<div class="table--tr block-group {if $smarty.foreach.stripeCreditCards.last}is--last-row{/if}">
							<div class="panel--td block"><strong>{$creditCard.name}</strong></div>
							<div class="panel--td block">{$creditCard.brand}</div>
							<div class="panel--td block">XXXXXXXXXXXX {$creditCard.last4}</div>
							<div class="panel--td block">{$creditCard.exp_month|string_format:"%02d"}/{$creditCard.exp_year}</div>
							<div class="panel--td block contains--button">
								<form name="stripeCreditCard-{$creditCard.id}" method="POST" action="{url controller='ViisonStripePaymentAccount' action='deleteCreditCard'}">
									<input type="hidden" name="cardId" value="{$creditCard.id}" />
									<button type="submit" class="btn is--primary is--small">{s name="credit_cards/table/actions/delete"}{/s}</button>
								</form>
							</div>
						</div>
					{/foreach}
				</div>
			</div>
		{else}
			{* No saved credit cards *}
			{capture name="viisonStripeNoCardsInfoText"}
				{s name="credit_cards/no_cards"}{/s}
			{/capture}
			{include file="frontend/_includes/messages.tpl" type="info" content=$smarty.capture.viisonStripeNoCardsInfoText}
		{/if}
	</div>
{/block}
