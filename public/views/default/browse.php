<div id="main-content">

<?php echo $language_select ?>

<ul class="breadcrumbs">
  <li><a href="<?php echo url::base() ?>"><?php echo L10n::_('Home') ?></a> &gt;</li>
  <li><a href="<?php echo $collection->getUrl() ?>"><?php echo $collection_display_name ?></a> &gt;</li>
  <li><?php echo L10n::_('Browse') ?></li>
</ul>

<h1>
  <?php printf( L10n::vsprintf('Browse by %s', array($page->getTitle()), true ) ) ?>
</h1>

<?php echo $tree ?>

</div>
