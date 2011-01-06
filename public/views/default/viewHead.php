<div id="main-content">

<?php echo $language_select ?>

<ul class="breadcrumbs">
  <li><a href="<?php echo url::base() ?>"><?php echo L10n::_('Home') ?></a> &gt;</li>
  <li>
  <a href="<?php echo $collection->getUrl() ?>">
  <?php echo $collection->getDisplayName( L10n::getLanguage() ) ?></a> &gt;
  </li>
  <li><?php echo L10n::_('View item') ?></li>
</ul>

<div id="document">

<h1><?php echo $root_node->getField('Title') ?></h1>

<?php if ($page->getCoverUrl()): ?>
<div id="cover-image">
  <img src="<?php echo $page->getCoverUrl() ?>" alt="cover image">
</div>
<?php endif; ?>
