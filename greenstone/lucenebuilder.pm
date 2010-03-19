###########################################################################
#
# lucenebuilder.pm -- perl wrapper for building index with Lucene
# A component of the Greenstone digital library software
# from the New Zealand Digital Library Project at the
# University of Waikato, New Zealand.
#
# Copyright (C) 1999 New Zealand Digital Library Project
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
#
###########################################################################

###########################################################################
# /*
#  *  @version 1.0 ?
#  *  @version 2.0 Incremental building assistance added, including
#  *               remove_document_from_database which implements the granddad's
#  *               empty function to call the lucene_passes.pl and full_lucene_passes_exe
#  *               so there is one place in the code that works out where the
#  *               perl script is. John Rowe
#  *
#  *  @author John Rowe, DL Consulting Ltd.
#  */
###########################################################################

package lucenebuilder;

# Use same basic XML structure setup by mgppbuilder/mgppbuildproc

use mgppbuilder;
use strict;
no strict 'refs';


sub BEGIN {
    @lucenebuilder::ISA = ('mgppbuilder');
}

# /**
#  *  @author  John Thompson, DL Consulting Ltd.
#  */
sub new {
    my $class = shift(@_);
    my $self = new mgppbuilder (@_);
    $self = bless $self, $class;

    $self->{'buildtype'} = "lucene";

    # Do we need to put exe on the end?
    my $exe = &util::get_os_exe ();
    my $scriptdir = "$ENV{'GSDLHOME'}/bin/script";

    # So where is lucene_passes.pl anyway?
    my $lucene_passes_script = &util::filename_cat($scriptdir, "lucene_passes.pl");

    # So tack perl on the beginning to ensure execution
    $self->{'full_lucene_passes'} = "$lucene_passes_script";
    if ($exe eq ".exe")
    {
	$self->{'full_lucene_passes_exe'} = "perl$exe \"$lucene_passes_script\"";
    }
    else
    {
	$self->{'full_lucene_passes_exe'} = "perl -S \"$lucene_passes_script\"";
    }

    return $self;
}
# /** new() **/

sub is_incremental_capable
{
    # lucene can do incremental building

    return 1;
}

sub init_for_incremental_build {
    my $self = shift (@_);

    # we want to read in indexfieldmap and indexfields from existing build.cfg
    # so that we know what has already been indexed
    my $buildcfg = $self->read_build_cfg();
    return unless defined $buildcfg;

    my $field;
    if (defined $buildcfg->{'indexfields'}) {
	foreach $field (@{$buildcfg->{'indexfields'}}) {
	    $self->{'buildproc'}->{'indexfields'}->{$field} = 1;
	}
    }

    if (defined $buildcfg->{'indexfieldmap'}) {
	foreach $field (@{$buildcfg->{'indexfieldmap'}}) {
	    my ($f, $v) = $field =~ /^(.*)\-\>(.*)$/;
	    $self->{'buildproc'}->{'indexfieldmap'}->{$f} = $v;
	}
    }

}

# lucene has none of these options
sub generate_index_options {
    my $self = shift (@_);

    $self->SUPER::generate_index_options();

    $self->{'casefold'} = 0;
    $self->{'stem'} = 0;
    $self->{'accentfold'} = 0;
    $self->{'stemindexes'} = 0;
}

sub default_buildproc {
    my $self  = shift (@_);

    return "lucenebuildproc";
}

# this writes a nice version of the text docs
sub compress_text
{
    my $self = shift (@_);
    # we don't do anything if we don't want compressed text
    return if $self->{'no_text'};

    my ($textindex) = @_;
    my $outhandle = $self->{'outhandle'};

    # the text directory
    my $text_dir = &util::filename_cat($self->{'build_dir'}, "text");
    my $build_dir = &util::filename_cat($self->{'build_dir'},"");
    &util::mk_all_dir ($text_dir);

    my $osextra = "";
    if ($ENV{'GSDLOS'} =~ /^windows$/i)
    {
	$text_dir =~ s@/@\\@g;
    }
    else
    {
	if ($outhandle ne "STDERR")
	{
	    # so lucene_passes doesn't print to stderr if we redirect output
	    $osextra .= " 2>/dev/null";
	}
    }

    # get any os specific stuff
    my $scriptdir = "$ENV{'GSDLHOME'}/bin/script";

    # Find the perl script to call to run lucene
    my $full_lucene_passes = $self->{'full_lucene_passes'};
    my $full_lucene_passes_exe = $self->{'full_lucene_passes_exe'};

    my $lucene_passes_sections = "Doc";

    my ($handle);

    if ($self->{'debug'})
    {
	$handle = *STDOUT;
    }
    else
    {
        print STDERR "Full Path:     $full_lucene_passes\n";
        print STDERR "Executable:    $full_lucene_passes_exe\n";
        print STDERR "Sections:      $lucene_passes_sections\n";
        print STDERR "Build Dir:     $build_dir\n";
        print STDERR "Cmd:           $full_lucene_passes_exe text $lucene_passes_sections \"$build_dir\" \"dummy\"   $osextra\n";
	if (!-e "$full_lucene_passes" ||
	    !open($handle, "| $full_lucene_passes_exe text $lucene_passes_sections \"$build_dir\" \"dummy\"   $osextra"))
	{
	    print STDERR "<FatalError name='NoRunLucenePasses'/>\n</Stage>\n" if $self->{'gli'};
	    die "lucenebuilder::build_index - couldn't run $full_lucene_passes_exe\n";
	}
    }

    # stored text is always Doc and Sec levels
    my $levels = { 'document' => 1, 'section' => 1 };
    # always do database at section level
    my $db_level = "section";

    # set up the document processr
    $self->{'buildproc'}->set_output_handle ($handle);
    $self->{'buildproc'}->set_mode ('text');
    $self->{'buildproc'}->set_index ($textindex);
    $self->{'buildproc'}->set_indexing_text (0);
    #$self->{'buildproc'}->set_indexfieldmap ($self->{'indexfieldmap'});
    $self->{'buildproc'}->set_levels ($levels);
    $self->{'buildproc'}->set_db_level ($db_level);
    $self->{'buildproc'}->reset();
    &plugin::begin($self->{'pluginfo'}, $self->{'source_dir'},
		   $self->{'buildproc'}, $self->{'maxdocs'});
    &plugin::read ($self->{'pluginfo'}, $self->{'source_dir'},
		   "", {}, {}, $self->{'buildproc'}, $self->{'maxdocs'}, 0, $self->{'gli'});
    &plugin::end($self->{'pluginfo'});
    close ($handle) unless $self->{'debug'};
    $self->print_stats();

    print STDERR "</Stage>\n" if $self->{'gli'};
}

sub build_indexes {
    my $self = shift (@_);
    my ($indexname) = @_;
    my $outhandle = $self->{'outhandle'};

    my $indexes = [];
    if (defined $indexname && $indexname =~ /\w/) {
	push @$indexes, $indexname;
    } else {
	$indexes = $self->{'collect_cfg'}->{'indexes'};
    }
    # have we got para index?
    foreach my $level (keys %{$self->{'levels'}}) {
	if ($level =~ /paragraph/) {
	    print $outhandle "Warning: Paragraph level indexing not supported by Lucene\n";
	    last;
	}
    }
    # create the mapping between the index descriptions
    # and their directory names (includes subcolls and langs)
    $self->{'index_mapping'} = $self->create_index_mapping ($indexes);

    # build each of the indexes
    foreach my $index (@$indexes) {
	if ($self->want_built($index)) {

	    my $idx = $self->{'index_mapping'}->{$index};
	    foreach my $level (keys %{$self->{'levels'}}) {
		next if $level =~ /paragraph/; # we don't do para indexing
		my ($pindex) = $level =~ /^(.)/;
		# should probably check that new name with level
		# is unique ... but currently (with doc sec and para)
		# each has unique first letter.
		$self->{'index_mapping'}->{$index} = $pindex.$idx;

		my $llevel = $mgppbuilder::level_map{$level};
		print $outhandle "\n*** building index $index at level $llevel in subdirectory " .
		    "$self->{'index_mapping'}->{$index}\n" if ($self->{'verbosity'} >= 1);
		print STDERR "<Stage name='Index' source='$index' level=$llevel>\n" if $self->{'gli'};

		$self->build_index($index,$llevel);
	    }
	    $self->{'index_mapping'}->{$index} = $idx;

	} else {
	    print $outhandle "\n*** ignoring index $index\n" if ($self->{'verbosity'} >= 1);
	}
    }

    #define the final field lists
    $self->make_final_field_list();
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

    if ($self->{'debug'}) {
	$handle = *STDOUT;
    } else {
	print STDERR "Cmd: $full_lucene_passes_exe $opt_create_index index $lucene_passes_sections \"$build_dir\" \"$indexdir\"   $osextra\n";
	if (!-e "$full_lucene_passes" ||
	    !open($handle, "| $full_lucene_passes_exe $opt_create_index index $lucene_passes_sections \"$build_dir\" \"$indexdir\"   $osextra")) {
	    print STDERR "<FatalError name='NoRunLucenePasses'/>\n</Stage>\n" if $self->{'gli'};
	    die "lucenebuilder::build_index - couldn't run $full_lucene_passes_exe\n";
	}
    }

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
    &plugin::read ($self->{'pluginfo'}, $self->{'source_dir'},
		   "", {}, {}, $self->{'buildproc'}, $self->{'maxdocs'}, 0, $self->{'gli'});
    close ($handle) unless $self->{'debug'};

    # EmeraldView: store raw text to build search snippets at runtime
    my $handle2;
    open( $handle2, "| php $ENV{'GSDLHOME'}/perllib/raw_text_extract.php \"$build_dir\" \"$indexdir\" " );
    $self->{'buildproc'}->set_output_handle ($handle2);
    &plugin::read ($self->{'pluginfo'}, $self->{'source_dir'},
		   "", {}, {}, $self->{'buildproc'}, $self->{'maxdocs'}, 0, $self->{'gli'});
    close ($handle2) unless $self->{'debug'};

    $self->print_stats();

    $self->{'buildproc'}->set_levels ($store_levels);
    print STDERR "</Stage>\n" if $self->{'gli'};
}

# /** A modified version of the basebuilder.pm's function that generates the
#  *  information database from the GA documents. We need to change this
#  *  so that if we've been asked to do an incremental build we only add
#  *  metadata to autohierarchy classifiers via the IncrementalBuildUtils
#  *  module. All other classifiers and metadata will be ignored.
#  */
# This was added to utilize DLC's incremental updating of Hierarchy classifiers. They are heading towards just using dynamic classifiers, and we do not want to use this code either. So now, we just use basebuilder's version of make_infodatabase
sub make_infodatabase_dlc
{
    my $self = shift (@_);
    my $outhandle = $self->{'outhandle'};

    # Get info database file path
    my $text_directory_path = &util::filename_cat($self->{'build_dir'}, "text");
    my $infodb_file_path = &dbutil::get_infodb_file_path($self->{'infodbtype'}, $self->{'collection'}, $text_directory_path);

    # If we aren't doing an incremental addition, then we just call the super-
    # classes version
    # Note: Incremental addition can only occur if an information database
    #       already exists. If it doesn't, let the super classes function be
    #       called once to generate it.
    if (!$self->{'incremental'} || !-e $infodb_file_path)
    {
        # basebuilder::make_infodatabase(@_);
        # Note: this doesn't work as the direct reference means all the $self
        #       data is lost.
        $self->basebuilder::make_infodatabase(@_);
        return;
    }

    # Carry on with an incremental addition
    print $outhandle "\n*** performing an incremental addition to the info database\n" if ($self->{'verbosity'} >= 1);
    print STDERR "<Stage name='CreateInfoData'>\n" if $self->{'gli'};

    # 1. Init all the classifiers
    &classify::init_classifiers ($self->{'classifiers'});
    # 2. Init the buildproc settings.
    #    Note: we still need this to process any associated files - but we
    #    don't expect to pipe anything to the database so we can do away with the
    #    complex output handle.
    my $assocdir = &util::filename_cat($self->{'build_dir'}, "assoc");
    &util::mk_all_dir ($assocdir);
    $self->{'buildproc'}->set_mode ('incinfodb'); # Very Important
    $self->{'buildproc'}->set_assocdir ($assocdir);
    # 3. Read in all the metadata from the files in the archives directory using
    #    the GAPlug and using ourselves as the document processor!
    &plugin::read ($self->{'pluginfo'}, $self->{'source_dir'}, "", {}, {}, $self->{'buildproc'}, $self->{'maxdocs'},0, $self->{'gli'});

    print STDERR "</Stage>\n" if $self->{'gli'};
}

# /** Lucene specific document removal function. This works by calling lucene_passes.pl with
#  *  -remove and the document id on the command line.
#  *
#  *  @param oid is the document identifier to be removed.
#  *
#  *  @author John Rowe, DL Consulting Ltd.
#  */
sub remove_document_from_database
{
    my ($self, $oid) = @_;
    # Find the perl script to call to run lucene
    my $full_lucene_passes_exe = $self->{'full_lucene_passes_exe'};
    # Call lucene_passes.pl with -remove and the document ID on the command line
    `$full_lucene_passes_exe -remove "$oid"`;
}
# /** remove_document_from_database **/


1;
