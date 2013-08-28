<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<!--[if IE]>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<![endif]-->
	
	<title><?= @$meta['title'] ?></title>
	
	<meta name="robots" content="<?= @$meta['robots'] ?>">
	<meta name="description" content="<?= @$meta['description'] ?>">

<?php if (@$meta['canonical']): ?>

	<link rel="canonical" href="<?= $meta['canonical'] ?>">

<?php endif; ?>

<?php if (count($load_css) > 0): foreach ($load_css as $href): ?>

	<link rel="stylesheet" href="<?= $href ?>"><?php endforeach; endif; ?>
	
</head>

<body class="<?= implode(' ', $body_classes) ?>">

