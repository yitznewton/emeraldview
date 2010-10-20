package solrbuilder;

# Use same basic XML structure setup by mgppbuilder/mgppbuildproc

use lucenebuilder;
use strict; 
no strict 'refs';


sub BEGIN {
    @solrbuilder::ISA = ('lucenebuilder');
}

sub build_index {
    my $self = shift (@_);
    my ($index,$llevel) = @_;
    my $outhandle = $self->{'outhandle'};
    my $build_dir = $self->{'build_dir'};

    # get the full index directory path and make sure it exists
    my $indexdir = $self->{'index_mapping'}->{$index};
    &util::mk_all_dir (&util::filename_cat($build_dir, $indexdir));

    # get any os specific stuff
    my $exedir = "$ENV{'GSDLHOME'}/bin/$ENV{'GSDLOS'}";
    my $scriptdir = "$ENV{'GSDLHOME'}/bin/script";

    # Find the perl script to call to run lucene
    my $full_lucene_passes = $self->{'full_lucene_passes'};
    my $full_lucene_passes_exe = $self->{'full_lucene_passes_exe'};

    # define the section names for lucenepasses
    # define the section names and possibly the doc name for lucenepasses
    my $lucene_passes_sections = $llevel;

    my $opt_create_index = ($self->{'incremental'}) ? "" : "-removeold";

    my $osextra = "";
    if ($ENV{'GSDLOS'} =~ /^windows$/i) {
	$build_dir =~ s@/@\\@g;
    } else {
	if ($outhandle ne "STDERR") {
	    # so lucene_passes doesn't print to stderr if we redirect output
	    $osextra .= " 2>/dev/null";
	}
    }

    # get the index expression if this index belongs
    # to a subcollection
    my $indexexparr = [];
    my $langarr = [];

    # there may be subcollection info, and language info.
    my ($fields, $subcollection, $language) = split (":", $index);
    my @subcollections = ();
    @subcollections = split /,/, $subcollection if (defined $subcollection);

    foreach $subcollection (@subcollections) {
	if (defined ($self->{'collect_cfg'}->{'subcollection'}->{$subcollection})) {
	    push (@$indexexparr, $self->{'collect_cfg'}->{'subcollection'}->{$subcollection});
	}
    }

    # add expressions for languages if this index belongs to
    # a language subcollection - only put languages expressions for the
    # ones we want in the index
    my @languages = ();
    my $languagemetadata = "Language";
    if (defined ($self->{'collect_cfg'}->{'languagemetadata'})) {
	$languagemetadata = $self->{'collect_cfg'}->{'languagemetadata'};
    }
    @languages = split /,/, $language if (defined $language);
    foreach my $language (@languages) {
	my $not=0;
	if ($language =~ s/^\!//) {
	    $not = 1;
	}
	if($not) {
	    push (@$langarr, "!$language");
	} else {
	    push (@$langarr, "$language");
	}
    }

    # Build index dictionary. Uses verbatim stem method
    print $outhandle "\n    creating index dictionary (lucene_passes -I1)\n"  if ($self->{'verbosity'} >= 1);
    print STDERR "<Phase name='CreatingIndexDic'/>\n" if $self->{'gli'};
    my ($handle);

    my $store_levels = $self->{'levels'};
    my $db_level = "section"; #always
    my $dom_level = "";
    foreach my $key (keys %$store_levels) {
	if ($mgppbuilder::level_map{$key} eq $llevel) {
	    $dom_level = $key;
	}
    }
    if ($dom_level eq "") {
	print STDERR "Warning: unrecognized tag level $llevel\n";
	$dom_level = "document";
    }

    my $local_levels = { $dom_level => 1 }; # work on one level at a time

    # set up the document processr
    $self->{'buildproc'}->set_output_handle ($handle);
    $self->{'buildproc'}->set_mode ('text');
    $self->{'buildproc'}->set_index ($index, $indexexparr);
    $self->{'buildproc'}->set_index_languages ($languagemetadata, $langarr) if (defined $language);
    $self->{'buildproc'}->set_indexing_text (1);
    #$self->{'buildproc'}->set_indexfieldmap ($self->{'indexfieldmap'});
    $self->{'buildproc'}->set_levels ($local_levels);
    $self->{'buildproc'}->set_db_level($db_level);
    $self->{'buildproc'}->reset();

    open( $handle, "| php $ENV{'GSDLHOME'}/perllib/solr_extract.php \"$build_dir\" \"$indexdir\" " );
    $self->{'buildproc'}->set_output_handle ($handle);
    &plugin::read ($self->{'pluginfo'}, $self->{'source_dir'},
		   "", {}, {}, $self->{'buildproc'}, $self->{'maxdocs'}, 0, $self->{'gli'});
    close ($handle) unless $self->{'debug'};

    $self->print_stats();

    $self->{'buildproc'}->set_levels ($store_levels);
    print STDERR "</Stage>\n" if $self->{'gli'};
}

1;
