<script type="text/javascript">
  $(document).ready( function() {
    chooseSearchForm( 'search-form-simple' );
  });
</script>

<div id="main-content">

<?php echo $language_select ?>

<ul class="breadcrumbs">
  <li><a href="<?php echo url::base() ?>"><?php echo L10n::_('Home') ?></a> &gt;</li>
  <li>
    <?php echo $collection_display_name ?>
  </li>
</ul>

<?php if ($collection->getClassifiers()): ?>

<div id="about-browse-container">

  <h2><?php echo L10n::_('Browse') ?></h2>

  <ul id="classifier-list">

  <?php
  foreach ( $collection->getClassifiers() as $classifier ) {
    $url = $classifier->getUrl();
    $raw_title = strtolower( L10n::_( $classifier->getTitle() ) );
    $display_title = L10n::vsprintf( ('By %s'), array( $raw_title ) );

    $classifier_link = myhtml::element('a', $display_title, array('href' => $url));

    echo myhtml::element('li', $classifier_link);
  }
  ?>

  </ul>

</div>

<?php endif; ?>

<div id="about-search-container">

  <h2><?php echo L10n::_('Search') ?></h2>

  <?php echo search::form_simple(  $collection ) ?>
  <?php echo search::form_fielded( $collection ) ?>
  <?php echo search::form_boolean( $collection ) ?>

  <ul id="search-form-chooser">
    <li>
      <a id="search-form-link-simple" href="#">
      <?php echo L10n::_('Simple') ?></a>
    </li>
    <li>
      | <a id="search-form-link-fielded" href="#">
      <?php echo L10n::_('Fielded') ?></a>
    </li>
    <li>
      | <a id="search-form-link-boolean" href="#">
      <?php echo L10n::_('Boolean') ?></a>
    </li>
  </ul>

</div>

<?php if ( ! empty( $search_history ) ): ?>

<div id="about-search-history-container">
  <h3><?php echo L10n::_('Recent searches') ?></h3>
  <?php echo search::history( $collection, $search_history ) ?>
</div>

<?php endif; ?>

<?php if ($collection_description): ?>
  <div id="about-description">
    <h2><?php echo L10n::_('About this collection') ?></h2>
    <p><?php echo $collection_description ?></p>
  </div>
<?php endif; ?>

</div>
