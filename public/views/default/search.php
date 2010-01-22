<div id="main-content">

<?php echo $language_select ?>

<ul class="breadcrumbs">
  <li><a href="<?php echo url::base() ?>"><?php echo L10n::_('Home') ?></a> &gt;</li>
  <li>
    <a href="<?php echo $collection->getUrl() ?>"><?php echo $collection_display_name ?></a> &gt;
  </li>
  <li>
    Search results for <strong><?php echo $query_builder->getDisplayQuery() ?></strong>
  </li>
</ul>

<div id="about-search-container">

  <h2><?php echo L10n::_('Search') ?></h2>

  <?php echo search::form_simple(  $collection, $query_builder ) ?>
  <?php //echo search::form_fielded( $collection ) ?>
  <?php //echo search::form_boolean( $collection ) ?>

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

<div id="search-results-container">
  <?php if ($hits_page->hits): ?>
    <div id="search-results-count">
      <?php echo search::result_summary( $hits_page, $query_builder->getDisplayQuery() ) ?>
    </div>
  <?php else: ?>
    <div id="search-results-fail">
      No results were found matching your search.
    </div>
  <?php endif; ?>

  <?php if ($hits_page->hits): ?>
  <ol id="search-hits" start="<?php echo $hits_page->firstHit ?>">
    <?php foreach ($hits_page->hits as $hit): ?>
      <li>
        <div>
          <a href="<?php echo $hit->link ?>">
            <?php echo search::highlight( $hit->title, $query_builder ) ?>
          </a>
        </div>

        <?php if ($hit->snippet): ?>
          <div class="search-snippet">
            <?php echo search::snippet( $hit, $query_builder ) ?>
          </div>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ol>

  <?php echo search::pager( $hits_page, $collection ) ?>
  <?php endif; ?>
</div>

</div>
