<?php include 'viewHead.php' ?>

<?php if ($page->getCoverUrl()): ?>
<div id="cover-image">
  <img src="<?php echo $page->getCoverUrl() ?>" alt="cover image">
</div>
<?php endif; ?>

<div id="image-pager">

<h2>
  <?php echo L10n::vsprintf( 'Page %s', array( $node->getField( 'Title' ) ) ) ?>
</h2>

<form id="pager-form" method="get" action="<?php echo $root_page->getUrl() ?>">

<script type="text/javascript">
  doc_url = '<?php echo $root_page->getUrl() ?>';
</script>

<?php if ($paged_urls['previous']): ?>
  <span class="prev-button">
    <a href="<?php echo $paged_urls['previous'] ?>">
    <?php echo L10n::_('Previous') ?></a>
  </span>
<?php else: ?>
  <span class="prev-button inactive">
    <?php echo L10n::_('Previous') ?>
  </span>
<?php endif; ?>

<?php printf(L10n::_('Go to page %s'), '<input type="text" name="page">') ?>
<input type="submit" value="<?php echo L10n::_('Go') ?>">

<?php if ($paged_urls['next']): ?>
  <span class="next-button">
  <a href="<?php echo $paged_urls['next'] ?>">
    <?php echo L10n::_('Next') ?></a>
  </span>
<?php else: ?>
  <span class="next-button inactive">
  <?php echo L10n::_('Next') ?>
  </span>
<?php endif; ?>

</form>

</div>

<?php $source_url = $page->getSourceDocumentUrl() ?>

<?php if ( $source_url && $page->getScreenIconUrl() ): ?>
<div id="main-image">
  <a href="<?php echo $source_url ?>">
    <img src="<?php echo $page->getScreenIconUrl() ?>"
    alt="page image" />
  </a>
</div>

<?php elseif ($source_url): ?>
<div id="source-link">
  <a href="<?php echo $source_url ?>">
    Download original document
  </a>
</div>
<?php endif; ?>

<div id="body-text">
  <?php echo $text ?>
</div>

<?php include 'viewFoot.php' ?>
