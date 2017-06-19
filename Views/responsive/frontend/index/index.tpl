{extends file="parent:frontend/index/index.tpl"}

{block name="frontend_index_header_javascript" append}
    <script type="text/javascript">
        if (typeof document.asyncReady !== 'undefined') {
            // Shopware >= 5.3, hence wait for async JavaScript first
            document.stripeJQueryReady = function(callback) {
                document.asyncReady(function() {
                    $(document).ready(callback);
                });
            };
        } else {
            // Shopware < 5.3, hence just wait for jQuery to be ready
            document.stripeJQueryReady = function(callback) {
                $(document).ready(callback);
            };
        }
    </script>
{/block}
