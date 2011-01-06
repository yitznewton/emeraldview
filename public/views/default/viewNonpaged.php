<?php include 'viewHead.php' ?>

<?php // begin TOC section ?>
<?php if ($tree): ?>
  <div id="toc">
    <div id="toc-header"><?php echo L10n::_('Table of contents') ?>
      <span class="toc-toggle" id="toc-hide" style="display:none">
        [<a href="#" onclick="return toggleTOC()"><?php echo L10n::_('hide') ?></a>]
      </span>
      <span class="toc-toggle" id="toc-show" style="display:none">
        [<a href="#" onclick="return toggleTOC()"><?php echo L10n::_('show') ?></a>]
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
  <?php if ( $node != $root_node ): ?>
    <h2><?php echo $node->getField( 'Title' ) ?></h2>
  <?php endif; ?>

  <?php echo $text ?>
</div>

<?php include 'viewFoot.php' ?>
