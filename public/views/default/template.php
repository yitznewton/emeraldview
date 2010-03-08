<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">

<html>

<head>
<title><?php echo $page_title ?></title>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<?php echo $css_includes ?>
<?php echo $js_includes ?>

</head>

<body dir="<?php echo L10n::_('ltr') ?>">

<div id="header">
  <?php if ( $method == 'index' ): ?>
    <span id="header-logo">
      <img src="<?php echo url::base() ?>images/emeraldview2.png"
      alt="EmeraldView logo">
    </span>
  <?php else: ?>
    <span id="header-logo">
      <a href="<?php echo url::base() ?>">
        <img src="<?php echo url::base() ?>images/emeraldview2.png"
        alt="EmeraldView logo">
      </a>
    </span>
  <?php endif; ?>

  <?php if ( isset($collection) ): ?>
    <div id="header-collection-title">
      <div><?php echo $collection_display_name ?></div>
    </div>
  <?php else: ?>
    <div id="header-emeraldview-title">
      <div>EmeraldView</div>
    </div>
  <?php endif; ?>
</div>

<?php echo $content ?>

<div id="footer">
  <div id="footer-url">URL: <?php echo url::site( url::current( true ) ) ?></div>

  <div>Copyright &copy; <?php echo date('Y') ?>

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
    <a href="http://bitbucket.org/yitznewton/emeraldview">EmeraldView</a>
  </div>
  
  <?php if ( ! IN_PRODUCTION ): ?>
  <div>
    Rendered in {execution_time} seconds, using {memory_usage} of memory
  </div>
  <?php endif; ?>
</div>

</body>
</html>