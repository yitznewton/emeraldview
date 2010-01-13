<script type="text/javascript">
  $(document).ready(function(){
    $(".browse-tree").treeview({
      collapsed: true,
      animated:  "fast"
    });
  });
</script>

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

<h1><?php echo $page->getNode()->getRootNode()->getField('Title') ?></h1>

<?php if ($page->getCoverUrl()): ?>
<div id="cover-image">
  <img src="<?php echo $page->getCoverUrl() ?>" alt="cover image">
</div>
<?php endif; ?>


<?php // begin TOC section ?>
<?php if ($tree): ?>
  <div id="toc">
    <div id="toc-header"><?php echo L10n::_('Table of contents') ?>
      <span class="toc-toggle" id="toc-toggle-hide">
        [<a href="#" id="toc-toggle-link" onclick="return toggleTOC()"><?php echo L10n::_('hide') ?></a>]
      </span>
      <span class="toc-toggle" id="toc-toggle-show" style="display:none">
        [<a href="#" id="toc-toggle-link" onclick="return toggleTOC()"><?php echo L10n::_('show') ?></a>]
      </span>
    </div>

    <div id="toc-container">
      <div id="tree-pager">
        <?php echo $tree_pager ?>
      </div>

      <?php echo $tree ?>

    </div>
  </div>
<?php endif; ?>

<?php var_dump($paged_urls) ?>
<?php if ($paged_urls): // begin PagedImage section ?>

<div id="image-pager">

<h2>
  Page <?php echo $page->getNode()->getField( 'title' ) ?>
</h2>

<form id="pager-form" method="get" action="<?php echo $page->getUrl() ?>"
onsubmit="return pageFormToUrl(this, '<?php echo $page->getUrl() ?>')">

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

<?php endif; // end PagedImage section ?>

<?php // FIXME is this the best possible if condition? ?>
<?php if ($page->getSourceDocumentUrl()
          && $page->getScreenIconUrl()): ?>
<div id="main-image">
  <a href="<?php echo $page->getSourceDocumentUrl() ?>">
    <img src="<?php echo $page->getScreenIconUrl() ?>"
    alt="page image" />
  </a>
</div>

<?php elseif ($page->getSourceDocumentUrl()): ?>
<div id="source-link">
  <a href="<?php echo $page->getSourceLink() ?>">
    Download original document
  </a>
</div>
<?php endif; ?>

<?php
  if (isset($_GET['search'])) {
    //$this->load->helper('search');
    //$text = highlight( $document->getHTML( $section_id ), $_GET['search'] );
  }
  else {
    $text = $page->getHTML();
  }
?>

<div id="body-text">
  <?php if (!$paged_urls && ( $page->getNode() != $page->getNode()->getRootNode() )): ?>
    <h2><?php echo $page->getNode()->getField( 'Title' ) ?></h2>
  <?php endif; ?>

  <?php echo $text ?>
</div>

<div class="clear"></div>

<?php if ($page->getNode()->getRootNode() != $page->getNode() && NodePage::factory( $page->getNode()->getRootNode() )->getDisplayMetadata()): ?>
<div class="metadata" dir="ltr">
  <h3><?php echo L10n::_('Document Metadata') ?></h3>

  <?php echo myview::metadata_list( NodePage::factory( $page->getNode()->getRootNode() )->getDisplayMetadata() ) ?>

  <div class="clear"></div>
</div>
<?php endif; ?>

<?php if ($page->getDisplayMetadata()): ?>
<div class="metadata" dir="ltr">
  <?php if ($page->getNode() == $page->getNode()->getRootNode()): ?>
  <h3><?php echo L10n::_('Document Metadata') ?></h3>
  <?php else: ?>
  <h3><?php echo L10n::_('Section Metadata') ?></h3>
  <?php endif; ?>

  <?php echo myview::metadata_list( $page->getDisplayMetadata() ) ?>

  <div class="clear"></div>
</div>
<?php endif; ?>

</div>
</div>
