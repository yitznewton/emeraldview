<div id="main-content">

<?php echo $language_select ?>

<h1><?php echo L10n::_('Welcome!') ?></h1>

<h4><?php echo L10n::_('The following collections are available:') ?></h4>

<ul id="collection-list">

  <?php foreach ($collections as $collection): ?>
    <li>
    <a href="<?php echo $collection->getUrl() ?>">
    <?php echo $collection->getDisplayName( $language ) ?>
    </a></li>
  <?php endforeach; ?>

</ul>

</div>
