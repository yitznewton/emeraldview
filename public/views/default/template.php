<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">

<html>

<head>
<title><?php echo $page_title ?></title>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<link rel="stylesheet" type="text/css"
href="<?php echo url::base() ?>views/<?php echo $theme ?>/css/reset.css">

<link rel="stylesheet" type="text/css" media="screen"
href="<?php echo url::base() ?>libraries/treeview/jquery.treeview.css">

<link rel="stylesheet" type="text/css"
href="<?php echo url::base() ?>views/<?php echo $theme ?>/css/style.css">

<link rel="stylesheet" type="text/css" media="print"
href="<?php echo url::base() ?>views/<?php echo $theme ?>/css/style-print.css">

<?php if (L10n::_('ltr') == 'rtl'): ?>
  <link rel="stylesheet" type="text/css"
  href="<?php echo url::base() ?>views/<?php echo $theme ?>/css/rtl.css">
<?php endif; ?>

<script type="text/javascript"
src="<?php echo url::base() ?>libraries/jquery.js"></script>

<script type="text/javascript"
src="<?php echo url::base() ?>libraries/treeview/jquery.treeview.js"></script>

<script type="text/javascript"
src="<?php echo url::base() ?>js/default.js"></script>

</head>

<body dir="<?php echo L10n::_('ltr') ?>">

<div id="header">
  <?php if (isset($collection)): ?>
    <span id="header-logo">
      <img src="<?php echo url::base() ?>images/emeraldview2.png"
      alt="EmeraldView logo" />
    </span>
    <div id="header-collection-title">
      <div>
        <?php echo $collection_display_name ?>
      </div>
    </div>
    <div class="clear"></div>
  <?php else: ?>
    <span id="header-logo">
      <a href="<?php echo url::base() ?>">
        <img src="<?php echo url::base() ?>images/emeraldview2.png"
        alt="EmeraldView logo" />
      </a>
    </span>
    <div id="header-emeraldview-title">
      <div>EmeraldView</div>
    </div>
  <?php endif; ?>

  <div class="clear"></div>
</div>

<?php echo $content ?>

<div id="footer">
  <?php // FIXME for homepage (reads '/collection'): ?>
  <div id="footer-url">URL: <?php echo url::site( url::current( true ) ) ?></div>

  <div>Copyright &copy; 2009

  <?php
  if (
    EmeraldviewConfig::get('institution_name')
    && EmeraldviewConfig::get('institution_url')
  ) {
    $inst_name = EmeraldviewConfig::get('institution_name');
    $inst_url  = EmeraldviewConfig::get('institution_url');

    echo 'by ';
    echo html::anchor( $inst_url, $inst_name );
  }
  elseif (EmeraldviewConfig::get('institution_name')) {
    echo 'by ' . EmeraldviewConfig::get('institution_name') ;
  }
  ?>

  </div>
  <div>Powered by
    <a href="http://www.greenstone.org/">Greenstone</a> and
    <a href="http://emeraldview.tourolib.org/">EmeraldView</a>
  </div>
  
  <div>
    Rendered in {execution_time} seconds, using {memory_usage} of memory
  </div>
</div>

</body>
</html>