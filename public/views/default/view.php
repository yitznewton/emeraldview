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

<h1><?php echo $document->getNode()->getField('Title') ?></h1>

<?php if ($document->getCoverUrl()): ?>
<div id="cover-image">
  <img src="<?php echo $document->getCoverUrl() ?>" alt="cover image">
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
        <?php
          // FIXME: refactor this whole mess
          /*
          $prev_url = $document->getIntervalUrl( $section_id, -1 );
          $next_url = $document->getIntervalUrl( $section_id, 1 );

          if ($prev_url) {
            echo html_element(
              'a', L10n::_('Previous page'), array('href' => $prev_url)
            );
          }
          else {
            echo html_element(
              'span', L10n::_('Previous page'), array('class' => 'inactive')
            );
          }

          if ($next_url) {
            echo html_element(
              'a', L10n::_('Next page'), array('href' => $next_url)
            );
          }
          else {
            echo html_element(
              'span', L10n::_('Next page'), array('class' => 'inactive')
            );
          }
          */
        ?>
      </div>

      <?php echo $tree ?>

    </div>
  </div>
<?php endif; ?>

<?php

// end TOC section

// begin paged image section
$pager = get_pager_controls( $document, $section_id );

if ($pager) {
  $document_root = $document->getSluggedUrl();
?>

<div id="pager">

<h2>
  Page <?php echo $document->getMetadataElement('Title', $section_id) ?>
</h2>

<form id="pager-form" method="get" action="<?php echo $document_root ?>"
onsubmit="return pageFormToUrl(this, '<?php echo $document_root ?>')">

<?php if ($pager['prev_url']): ?>
  <span class="prev-button">
  <a href="<?php echo $pager['prev_url'] ?>">
    <?php echo L10n::_('Previous') ?></a>
<?php else: ?>
  <span class="prev-button inactive">
  <?php echo L10n::_('Previous') ?>
<?php endif; ?>

</span>

<?php printf(L10n::_('Go to page %s'), '<input type="text" name="page">') ?>
<input type="submit" value="<?php echo L10n::_('Go') ?>">

<?php if ($pager['next_url']): ?>
  <span class="next-button">
  <a href="<?php echo $pager['next_url'] ?>">
    <?php echo L10n::_('Next') ?></a>
  </span>
<?php else: ?>
  <span class="next-button inactive">
  <?php echo L10n::_('Next') ?>
  </span>
<?php endif; ?>

</form>

</div>

<?php
}
// end paged image section
?>

<?php if ($document->getSourceUrl( $section_id )
          && $document->getScreenIconUrl( $section_id )): ?>
<div id="main-image">
  <a href="<?php echo $document->getSourceUrl( $section_id ) ?>">
    <img src="<?php echo $document->getScreenIconUrl( $section_id ) ?>"
    alt="page image" />
  </a>
</div>

<?php elseif ($document->getSourceUrl( $section_id )): ?>
<div id="source-link">
  <a href="<?php echo $document->getSourceLink( $section_id ) ?>">
    Download original document
  </a>
</div>
<?php endif; ?>

<?php
  if (isset($_GET['search'])) {
    $this->load->helper('search');
    $text = highlight( $document->getHTML( $section_id ), $_GET['search'] );
  }
  else {
    $text = $document->getHTML( $section_id );
  }
?>

<div id="body-text">
  <?php if (!$pager && $section_id): ?>
    <h2>
    <?php echo $document->getMetadataElement('Title', $section_id) ?>
    </h2>
  <?php endif; ?>

  <?php echo $text ?>
</div>

<div class="clearer"></div>

<?php if ($toc): ?>
  <div id="bottom-pager">
    <?php
      $prev_url = $document->getIntervalUrl( $section_id, -1 );
      $next_url = $document->getIntervalUrl( $section_id, 1 );

      if ($prev_url) {
        echo html_element(
          'a', L10n::_('Previous page'), array('href' => $prev_url)
        );
      }
      else {
        echo html_element(
          'span', L10n::_('Previous page'), array('class' => 'inactive')
        );
      }

      if ($next_url) {
        echo html_element(
          'a', L10n::_('Next page'), array('href' => $next_url)
        );
      }
      else {
        echo html_element(
          'span', L10n::_('Next page'), array('class' => 'inactive')
        );
      }
    ?>
  </div>

<?php elseif ($pager): ?>

<div id="bottom-pager">

<?php if ($pager['prev_url']): ?>
  <span class="prev-button">
  <a href="<?php echo $pager['prev_url'] ?>">
    <?php echo L10n::_('Previous') ?></a>
<?php else: ?>
  <span class="prev-button inactive">
  <?php echo L10n::_('Previous') ?>
<?php endif; ?>

</span>

<?php if ($pager['next_url']): ?>
  <span class="next-button">
  <a href="<?php echo $pager['next_url'] ?>">
    <?php echo L10n::_('Next') ?></a>
<?php else: ?>
  <span class="next-button inactive">
  <?php echo L10n::_('Next') ?>
<?php endif; ?>

</span>

</div>

<?php endif; ?>

<?php if (
  $collection->getConfig('display_metadata')
  && $document->getMetadata()
)?>

<?php if ($document->getDisplayMetadata()): ?>

<div class="metadata" dir="ltr">
  <h3><?php echo L10n::_('Document Metadata') ?></h3>

  <?php echo metadata_list( $document->getDisplayMetadata() ) ?>

  <div class="clearer"></div>
</div>

<?php endif; ?>

<?php if ($section_id && $document->getDisplayMetadata( $section_id )): ?>
<div class="metadata" dir="ltr">
  <h3><?php echo L10n::_('Section Metadata') ?></h3>

  <?php echo metadata_list( $document->getDisplayMetadata( $section_id ) ) ?>

  <div class="clearer"></div>
</div>
<?php endif; ?>


</div>
</div>
