<style>
    .lob-preview {
        width: 900px;
        height: 600px;
    }
</style>
<h2>Front</h2>
<iframe src="/admin/orders/fulfillment/lob?action=preview&line_item_id=<?= $line_item_id; ?>&side=front" class="lob-preview"></iframe>
<h2>Back</h2>
<iframe src="/admin/orders/fulfillment/lob?action=preview&line_item_id=<?= $line_item_id; ?>&side=back" class="lob-preview"></iframe>
