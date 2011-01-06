<div class="clear"></div>

<?php if ($root_node != $node && $root_page->getDisplayMetadata()): ?>
<div class="metadata" dir="ltr">
  <h3><?php echo L10n::_('Document Metadata') ?></h3>

  <?php echo myview::metadata_list( $root_page->getDisplayMetadata() ) ?>

  <div class="clear"></div>
</div>
<?php endif; ?>

<?php if ($page->getDisplayMetadata()): ?>
<div class="metadata" dir="ltr">
  <?php if ($node == $root_node): ?>
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
