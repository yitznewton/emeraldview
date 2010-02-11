/*
function pageFormToUrl(form, base_url)
{
  page_number = form.page.value;
  re_pattern = new RegExp('^\\d+$');

  if (re_pattern.test(page_number)) {
    window.location = base_url + page_number;
    return false;
  }

  return false;
}

function clearForm(form)
{
  for (i = 0; i < form.elements.length; i++) {
    if (form.elements[i].type == 'text') {
        form.elements[i].value = '';
    }
  }

  return false;
}

function chooseForm(e)
{
  id = e.data.form_id;

  if (e.data.speed === undefined) {
	  speed = null;
  }
  else {
    speed = e.data.speed;
  }

  $('form.search-form').each(function() {
  	if (this.id == id) {
      $(this).show( speed );
  	}
  	else {
      $(this).hide( speed );
  	}
  })

  return false;
}

*/

function chooseSearchForm( id, slide )
{
  if ( slide == undefined ) {
    slide = false;
  }

  $('form.search-form').each( function() {
    if ( this.id == id ) {
      if (slide) {
        $(this).slideDown();
      }
      else {
        $(this).show();
      }
    }
    else {
      if (slide) {
        $(this).slideUp();
      }
      else {
        $(this).hide();
      }
    }
  });
}

function changeLanguage( language_select )
{
  langcode = language_select.value;
  path = window.location.pathname;

  querystring = window.location.search.substring(1);
  querystring = querystring.replace( /&?language=\w+/, '' );

  if (querystring) {
    querystring += '&language=' + language_select.value;
  }
  else {
    querystring = 'language=' + language_select.value;
  }

  window.location = path + '?' + querystring;

  return true;
}

function toggleTOC()
{
  hide_span = $('#toc-toggle-hide');
  show_span = $('#toc-toggle-show');
  isTocShown  = (hide_span.css('display') == 'inline') ? true : false;

  if (isTocShown) {
    $('#toc-container').hide();
    hide_span.hide();
    show_span.show();
  }
  else {
    $('#toc-container').show();
    hide_span.show();
    show_span.hide();
  }

  return false;
}

$(document).ready( function() {
  $('#language-select-select').change( function() {
    changeLanguage( this );
  });

  $('#language-select-submit').hide();

  $('#toc-hide').show();

  $('#toc-hide a').click( function() {
    $('#toc-hide').hide();
    $('#toc-show').show();
    $('#toc-container').hide();
  });

  $('#toc-show a').click( function() {
    $('#toc-hide').show();
    $('#toc-show').hide();
    $('#toc-container').show();
  });

  $('#search-form-link-simple').click( function() {
    chooseSearchForm( 'search-form-simple', true );
  });

  $('#search-form-link-fielded').click( function() {
    chooseSearchForm( 'search-form-fielded', true );
  });

  $('#search-form-link-boolean').click( function() {
    chooseSearchForm( 'search-form-boolean', true );
  });
});