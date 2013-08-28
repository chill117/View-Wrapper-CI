
<?php if (isset($js_variables) && count($js_variables) > 0): ?>
	
	<script><?php foreach ($js_variables as $variable => $value): ?>

		var <?= $variable ?> = <?= json_encode($value) ?>;<?php endforeach; ?>

	</script><?php endif; ?>


<?php if (count($load_js) > 0): foreach ($load_js as $src): ?>

	<script src="<?= $src ?>"></script><?php endforeach; endif; ?>

</body>
</html>