<script>
document.querySelectorAll('[data-total-calculator]').forEach((wrap) => {
    const qty = wrap.querySelector(`[name="${wrap.dataset.qtyField}"]`);
    const rate = wrap.querySelector(`[name="${wrap.dataset.rateField}"]`);
    const output = wrap.querySelector('[data-total-output]');
    const update = () => output.value = ((Number(qty.value) || 0) * (Number(rate.value) || 0)).toFixed(2);

    qty?.addEventListener('input', update);
    rate?.addEventListener('input', update);
    update();
});
</script>
