<?php
if ( isset( $search_handler ) ) {
  switch ( get_class( $search_handler->getQuery() ) ) {
    case 'Query_Simple':
      $form_id = 'search-form-simple';
      break;
    case 'Query_Fielded':
      $form_id = 'search-form-fielded';
      break;
    case 'Query_Boolean':
      $form_id = 'search-form-boolean';
      break;
    default:
      $form_id = 'search-form-simple';
  }
}
else {
  $form_id = 'search-form-simple';
}
?>

<script type="text/javascript">
  $(document).ready( function() {
    chooseSearchForm( "<?php echo $form_id ?>" );
  });
</script>

<div id="main-content">

<?php echo $language_select ?>

<ul class="breadcrumbs">
  <li><a href="<?php echo url::base() ?>"><?php echo L10n::_('Home') ?></a> &gt;</li>
  <li>
    <a href="<?php echo $collection->getUrl() ?>"><?php echo $collection_display_name ?></a> &gt;
  </li>
  <li>
    Search results for <strong><?php echo $search_handler->getQuery()->getDisplayQuery() ?></strong>
  </li>
</ul>

<div id="about-search-outer-container">
<div id="about-search-container">
  <h2><?php echo L10n::_('Search') ?></h2>

  <?php echo search::form_simple(  $collection, $search_handler ) ?>
  <?php echo search::form_fielded( $collection, $search_handler ) ?>
  <?php echo search::form_boolean( $collection, $search_handler ) ?>
  <?php echo search::chooser() ?>
</div>
</div>

<?php if ( ! empty( $search_history ) ): ?>
  <div id="search-history-container">
    <h3><?php echo L10n::_('Recent searches') ?></h3>
    <?php echo search::history( $collection, $search_history ) ?>
  </div>
<?php endif; ?>

<div id="search-results-container">
  <div id="search-results-count">
    <?php echo search::result_summary( $hits_page, $search_handler ) ?>
  </div>

  <?php echo search::pager( $hits_page, $collection ) ?>
  
  <?php if ($hits_page->hits): ?>
  <ol id="search-hits" start="<?php echo $hits_page->firstHit ?>">
    <?php foreach ($hits_page->hits as $hit): ?>
      <li>
        <div><?php echo $hit->link ?></div>

        <?php if ($hit->snippet): ?>
          <div class="search-snippet"><?php echo $hit->snippet ?></div>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ol>

  <?php echo search::pager( $hits_page, $collection ) ?>

  <?php endif; ?>
</div>

</div>
