<h2>Include Items:</h2>
<form method="post">
    <?= \Lightning\Tools\Form::renderTokenInput(); ?>
    <input type="hidden" name="id" value="<?= $order->id; ?>">
    <table width="100%">
        <thead>
        <tr>
            <td><input type="checkbox" checked="checked"></td>
            <td>Product</td>
            <td>Options</td>
        </tr>
        </thead>
        <?php foreach ($order->getItems() as $item): ?>
            <tr>
                <td><input type="checkbox" checked="checked" name="checkout_order_item[<?= $item->id; ?>" value="1" /></td>
                <td><?= $item->getProduct()->title; ?></td>
                <td><?= $item->getHTMLFormattedOptions(); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <input type="submit" name="submit" value="Fulfill Order" class="button medium">
</form>
