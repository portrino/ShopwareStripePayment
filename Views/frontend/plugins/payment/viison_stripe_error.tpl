{block name="frontend_index_content_top" append}
{if $viisonStripePaymentError}
<div class="grid_20 first">
	<div class="error">
		<div class="center">Beim Bezahlen der Bestellung ist ein Fehler aufgetreten!</div>
		<br />
		<div class="normal">{$viisonStripePaymentError}</div>
	</div>
</div>
{/if}
{/block}
