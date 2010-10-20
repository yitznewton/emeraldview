#!/usr/bin/perl -w

## @file buildcol.pl
# This program will build a particular collection.
# A component of the Greenstone digital library software
# from the New Zealand Digital Library Project at the 
# University of Waikato, New Zealand.
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
# @author New Zealand Digital Library Project unless otherwise stated
# @copy 1999 New Zealand Digital Library Project
#
package buildcol;

BEGIN {
    die "GSDLHOME not set\n" unless defined $ENV{'GSDLHOME'};
    die "GSDLOS not set\n" unless defined $ENV{'GSDLOS'};
    unshift (@INC, "$ENV{'GSDLHOME'}/perllib");
    unshift (@INC, "$ENV{'GSDLHOME'}/perllib/cpan");
    unshift (@INC, "$ENV{'GSDLHOME'}/perllib/cpan/perl-5.8");
    unshift (@INC, "$ENV{'GSDLHOME'}/perllib/cpan/XML/XPath");
    unshift (@INC, "$ENV{'GSDLHOME'}/perllib/plugins");
    unshift (@INC, "$ENV{'GSDLHOME'}/perllib/classify");

    if (defined $ENV{'GSDLEXTS'}) {
	my @extensions = split(/:/,$ENV{'GSDLEXTS'});
	foreach my $e (@extensions) {
	    my $ext_prefix = "$ENV{'GSDLHOME'}/ext/$e";

	    unshift (@INC, "$ext_prefix/perllib");
	    unshift (@INC, "$ext_prefix/perllib/cpan");
	    unshift (@INC, "$ext_prefix/perllib/plugins");
	    unshift (@INC, "$ext_prefix/perllib/plugouts");
	    unshift (@INC, "$ext_prefix/perllib/classify");
	}
    }
}

use colcfg;
use util;
use scriptutil;
use FileHandle;
use gsprintf;
use printusage;
use parse2;

use strict;
no strict 'refs'; # allow filehandles to be variables and vice versa
no strict 'subs'; # allow barewords (eg STDERR) as function arguments


my $mode_list =
    [ { 'name' => "all",
        'desc' => "{buildcol.mode.all}" },
      { 'name' => "compress_text",
        'desc' => "{buildcol.mode.compress_text}" },
      { 'name' => "build_index",
        'desc' => "{buildcol.mode.build_index}" },
      { 'name' => "infodb",
        'desc' => "{buildcol.mode.infodb}" } ];

my $sec_index_list = 
    [ {'name' => "never",
       'desc' => "{buildcol.sections_index_document_metadata.never}" },
      {'name' => "always",
       'desc' => "{buildcol.sections_index_document_metadata.always}" },
      {'name' => "unless_section_metadata_exists",
       'desc' => "{buildcol.sections_index_document_metadata.unless_section_metadata_exists}" }
      ];

my $arguments =
    [ { 'name' => "remove_empty_classifications",
	'desc' => "{buildcol.remove_empty_classifications}",
	'type' => "flag",
	'reqd' => "no",
	'modegli' => "2" },
      { 'name' => "archivedir",
	'desc' => "{buildcol.archivedir}",
	'type' => "string",
	'reqd' => "no",
        'hiddengli' => "yes" },
      { 'name' => "builddir",
	'desc' => "{buildcol.builddir}",
	'type' => "string",
	'reqd' => "no",
        'hiddengli' => "yes" },
#     { 'name' => "cachedir",
#	'desc' => "{buildcol.cachedir}",
#	'type' => "string",
#	'reqd' => "no" },
      { 'name' => "collectdir",
	'desc' => "{buildcol.collectdir}",
	'type' => "string",
	# parsearg left "" as default
	#'deft' => &util::filename_cat ($ENV{'GSDLHOME'}, "collect"),
	'reqd' => "no",
        'hiddengli' => "yes" },
      { 'name' => "site",
	'desc' => "{buildcol.site}",
	'type' => "string",
	'deft' => "",
	'reqd' => "no",
        'hiddengli' => "yes" },
      { 'name' => "debug",
	'desc' => "{buildcol.debug}",
	'type' => "flag",
	'reqd' => "no",
        'hiddengli' => "yes" },
      { 'name' => "faillog",
	'desc' => "{buildcol.faillog}",
	'type' => "string",
	# parsearg left "" as default
	#'deft' => &util::filename_cat("<collectdir>", "colname", "etc", "fail.log"),
	'reqd' => "no",
	'modegli' => "3" },
      { 'name' => "index",
	'desc' => "{buildcol.index}",
	'type' => "string",
	'reqd' => "no",
	'modegli' => "3" },
      { 'name' => "incremental",
	'desc' => "{buildcol.incremental}",
	'type' => "flag",
	'hiddengli' => "yes" },
      { 'name' => "keepold",
	'desc' => "{buildcol.keepold}",
	'type' => "flag",
	'reqd' => "no",
        #'modegli' => "3",
	'hiddengli' => "yes" },
      { 'name' => "removeold",
	'desc' => "{buildcol.removeold}",
	'type' => "flag",
	'reqd' => "no",
	#'modegli' => "3",
	'hiddengli' => "yes"  },
      { 'name' => "language",
	'desc' => "{scripts.language}",
	'type' => "string",
	'reqd' => "no",
	'modegli' => "3" },
      { 'name' => "maxdocs",
	'desc' => "{buildcol.maxdocs}",
	'type' => "int",
	'reqd' => "no",
        'hiddengli' => "yes" },
      { 'name' => "maxnumeric",
	'desc' => "{buildcol.maxnumeric}",
	'type' => "int",
	'reqd' => "no",
	'deft' => "4",
	'range' => "4,512",
	'modegli' => "3" },
      { 'name' => "mode",
	'desc' => "{buildcol.mode}",
	'type' => "enum",
	'list' => $mode_list,
	# parsearg left "" as default
#	'deft' => "all",
	'reqd' => "no",
	'modegli' => "3" },
      { 'name' => "no_strip_html",
	'desc' => "{buildcol.no_strip_html}",
	'type' => "flag",
	'reqd' => "no",
	'modegli' => "3" },
      { 'name' => "no_text",
	'desc' => "{buildcol.no_text}",
	'type' => "flag",
	'reqd' => "no",
	'modegli' => "2" },
      { 'name' => "sections_index_document_metadata",
	'desc' => "{buildcol.sections_index_document_metadata}",
	'type' => "enum",
	'list' => $sec_index_list,
	'reqd' => "no",
	'modegli' => "2" },
      { 'name' => "out",
	'desc' => "{buildcol.out}",
	'type' => "string",
	'deft' => "STDERR",
	'reqd' => "no",
        'hiddengli' => "yes" },
      { 'name' => "verbosity",
	'desc' => "{buildcol.verbosity}",
	'type' => "int",
	# parsearg left "" as default
	#'deft' => "2",
	'reqd' => "no",
	'modegli' => "3" },
      { 'name' => "gli",
	'desc' => "",
	'type' => "flag",
	'reqd' => "no",
	'hiddengli' => "yes" },
      { 'name' => "xml",
	'desc' => "{scripts.xml}",
	'type' => "flag",
	'reqd' => "no",
	'hiddengli' => "yes" },
      { 'name' => "disable_OAI",
        'desc' => "{buildcol.disable_OAI}",
        'type' => "flag",
        'reqd' => "no",
        'modegli' => "2",
	'hiddengli' => "yes" },
      ];

my $options = { 'name' => "buildcol.pl",
		'desc' => "{buildcol.desc}",
		'args' => $arguments };


# globals
my $collection;
my $configfilename;
my $out;

# used to signify "gs2"(default) or "gs3"
my $gs_mode = "gs2";

## @method gsprintf()
#  Print a string to the screen after looking it up from a locale dependant
#  strings file. This function is losely based on the idea of resource
#  bundles as used in Java.
#
#  @param  $error The STDERR stream.
#  @param  $text The string containing GS keys that should be replaced with
#                their locale dependant equivilents.
#  @param  $out The output stream.
#  @return The locale-based string to output.
#
sub gsprintf()
{
    return &gsprintf::gsprintf(@_);
}
## gsprintf() ##

&main();

## @method main()
#
#  [Parses up and validates the arguments to the build process before creating
#  the appropriate build process to do the actual work - John]
#
#  @note Added true incremental support - John Thompson, DL Consulting Ltd.
#  @note There were several bugs regarding using directories other than 
#        "import" or "archives" during import and build quashed. - John 
#        Thompson, DL Consulting Ltd.
#
#  @param  $incremental If true indicates this build should not regenerate all
#                       the index and metadata files, and should instead just
#                       append the information found in the archives directory
#                       to the existing files. If this requires some complex
#                       work so as to correctly insert into a classifier so be
#                       it. Of course none of this is done here - instead the
#                       incremental argument is passed to the document
#                       processor.
#
sub main
{
    # command line args
    my ($verbosity, $archivedir, $cachedir, $builddir, $site, $maxdocs, 
	$debug, $mode, $indexname, $removeold, $keepold, 
	$incremental, $incremental_mode,
	$remove_empty_classifications,
	$collectdir, $build, $type, $textindex,
	$no_strip_html, $no_text, $faillog, $gli, $index, $language,
	$sections_index_document_metadata, $maxnumeric,
	$disable_OAI);

    my $xml = 0;
    my $hashParsingResult = {};
    # general options available to all plugins
    my $intArgLeftinAfterParsing = parse2::parse(\@ARGV,$arguments,$hashParsingResult,"allow_extra_options");

    # If parse returns -1 then something has gone wrong
    if ($intArgLeftinAfterParsing == -1)
    {
	&PrintUsage::print_txt_usage($options, "{buildcol.params}");
	die "\n";
    }
    
    foreach my $strVariable (keys %$hashParsingResult)
    {
	eval "\$$strVariable = \$hashParsingResult->{\"\$strVariable\"}";
    }

    # If $language has been specified, load the appropriate resource bundle
    # (Otherwise, the default resource bundle will be loaded automatically)
    if ($language && $language =~ /\S/) {
	&gsprintf::load_language_specific_resource_bundle($language);
    }

    if ($xml) {
        &PrintUsage::print_xml_usage($options);
	print "\n";
	return;
    }

    if ($gli) { # the gli wants strings to be in UTF-8
	&gsprintf::output_strings_in_UTF8; 
    }

    # now check that we had exactly one leftover arg, which should be 
    # the collection name. We don't want to do this earlier, cos 
    # -xml arg doesn't need a collection name
    # Or if the user specified -h, then we output the usage also
    if ($intArgLeftinAfterParsing != 1 || (@ARGV && $ARGV[0] =~ /^\-+h/))
    {
	&PrintUsage::print_txt_usage($options, "{buildcol.params}");
	die "\n";
    }
    
    $textindex = "";
    my $close_out = 0;
    if ($out !~ /^(STDERR|STDOUT)$/i) {
	open (OUT, ">$out") ||
	    (&gsprintf(STDERR, "{common.cannot_open_output_file}\n", $out) && die);
	$out = "buildcol::OUT";
	$close_out = 1;
    }
    $out->autoflush(1);

    # get and check the collection
    if (($collection = &colcfg::use_collection($site, @ARGV, $collectdir)) eq "") {
	&PrintUsage::print_txt_usage($options, "{buildcol.params}");
	die "\n";
    }

    if ($faillog eq "") {
	$faillog = &util::filename_cat($ENV{'GSDLCOLLECTDIR'}, "etc", "fail.log");
    }
    # note that we're appending to the faillog here (import.pl clears it each time)
    # this could potentially create a situation where the faillog keeps being added 
    # to over multiple builds (if the import process is being skipped)
    open (FAILLOG, ">>$faillog") ||
	(&gsprintf(STDERR, "{common.cannot_open_fail_log}\n", $faillog) && die);
    $faillog = 'buildcol::FAILLOG';
    $faillog->autoflush(1);

    unshift (@INC, "$ENV{'GSDLCOLLECTDIR'}/perllib");
    # Don't know why this didn't already happen, but now collection specific
    # classify and plugins directory also added to include path
    unshift (@INC, "$ENV{'GSDLCOLLECTDIR'}/perllib/classify");
    unshift (@INC, "$ENV{'GSDLCOLLECTDIR'}/perllib/plugins");

    # Read in the collection configuration file.
    my ($collectcfg, $buildtype); 
    ($configfilename, $gs_mode) = &colcfg::get_collect_cfg_name($out);
    $collectcfg = &colcfg::read_collection_cfg ($configfilename, $gs_mode);

    if ($verbosity !~ /\d+/) {
	if (defined $collectcfg->{'verbosity'} && $collectcfg->{'verbosity'} =~ /\d+/) {
	    $verbosity = $collectcfg->{'verbosity'};
	} else {
	    $verbosity = 2; # the default
	}
    }
    # we use searchtype for determining buildtype, but for old versions, use buildtype
    if (defined $collectcfg->{'buildtype'}) {
	$buildtype = $collectcfg->{'buildtype'};
    } elsif (defined $collectcfg->{'searchtypes'} || defined $collectcfg->{'searchtype'}) {
	$buildtype = "mgpp";
    } else {
	$buildtype = "mg"; #mg is the default
    }
    if (defined $collectcfg->{'archivedir'} && $archivedir eq "") {
	$archivedir = $collectcfg->{'archivedir'};
    }
    if (defined $collectcfg->{'cachedir'} && $cachedir eq "") {
	$cachedir = $collectcfg->{'cachedir'};
    }
    if (defined $collectcfg->{'builddir'} && $builddir eq "") {
	$builddir = $collectcfg->{'builddir'};
    }
    if ($maxdocs !~ /\-?\d+/) {
	if (defined $collectcfg->{'maxdocs'} && $collectcfg->{'maxdocs'} =~ /\-?\d+/) {
	    $maxdocs = $collectcfg->{'maxdocs'};
	} else {
	    $maxdocs = -1; # the default
	}
    }
    if (defined $collectcfg->{'maxnumeric'} && $collectcfg->{'maxnumeric'} =~ /\d+/) {
	$maxnumeric = $collectcfg->{'maxnumeric'};
    } 
    
    if ($maxnumeric < 4 || $maxnumeric > 512) {
	$maxnumeric = 4;
    }
    
    if (defined $collectcfg->{'debug'} && $collectcfg->{'debug'} =~ /^true$/i) {
	$debug = 1;
    }
    if ($mode !~ /^(all|compress_text|build_index|infodb)$/) {
	if (defined $collectcfg->{'mode'} && $collectcfg->{'mode'} =~ /^(all|compress_text|build_index|infodb)$/) {
	    $mode = $collectcfg->{'mode'};
	} else {
	    $mode = "all"; # the default
	}
    }
    if (defined $collectcfg->{'index'} && $indexname eq "") {
	$indexname = $collectcfg->{'index'};
    }
    if (defined $collectcfg->{'no_text'} && $no_text == 0) {
	if ($collectcfg->{'no_text'} =~ /^true$/i) {
	    $no_text = 1;
	}
    }
    if (defined $collectcfg->{'no_strip_html'} && $no_strip_html == 0) {
	if ($collectcfg->{'no_strip_html'} =~ /^true$/i) {
	    $no_strip_html = 1;
	}
    }
    if (defined $collectcfg->{'remove_empty_classifications'} && $remove_empty_classifications == 0) {
	if ($collectcfg->{'remove_empty_classifications'} =~ /^true$/i) {
	    $remove_empty_classifications = 1;
	}
    }
    
    if ($buildtype eq "mgpp" && defined $collectcfg->{'textcompress'}) {
	$textindex = $collectcfg->{'textcompress'};
    }
    if (defined $collectcfg->{'gli'} && $collectcfg->{'gli'} =~ /^true$/i) {
	$gli = 1;
    }

    if ($sections_index_document_metadata !~ /\S/ && defined $collectcfg->{'sections_index_document_metadata'}) {
	$sections_index_document_metadata = $collectcfg->{'sections_index_document_metadata'};
    }
    
    if ($sections_index_document_metadata !~ /^(never|always|unless_section_metadata_exists)$/) {
	$sections_index_document_metadata = "never";
    }
    
    ($removeold, $keepold, $incremental, $incremental_mode) 
	= &scriptutil::check_removeold_and_keepold($removeold, $keepold, 
						   $incremental, "building", 
						   $collectcfg);
 
    $gli = 0 unless defined $gli;

    # If the disable_OAI flag is not present, the option $disable_OAI with the value of 0 will be passed to basebuilder.pm
    $disable_OAI = 0 unless defined $disable_OAI;
    
    # New argument to track whether build is incremental
    $incremental = 0 unless defined $incremental;

    print STDERR "<Build>\n" if $gli;

    #set the text index
    if (($buildtype eq "mgpp") || ($buildtype =~ /lucene/) || ($buildtype eq "solr")) {
	if ($textindex eq "") {
	    $textindex = "text";
	}
    }
    else {
	$textindex = "section:text";
    }

    # fill in the default archives and building directories if none
    # were supplied, turn all \ into / and remove trailing /

    my ($realarchivedir, $realbuilddir);
    # Modified so that the archivedir, if provided as an argument, is made
    # absolute if it isn't already
    if ($archivedir eq "")
      {
        $archivedir = &util::filename_cat ($ENV{'GSDLCOLLECTDIR'}, "archives");
      }
    else
      {
        $archivedir = &util::make_absolute($ENV{'GSDLCOLLECTDIR'}, $archivedir);
      }
    # End Mod
    $archivedir =~ s/[\\\/]+/\//g;
    $archivedir =~ s/\/$//;

    if ($builddir eq "") {
	$builddir = &util::filename_cat ($ENV{'GSDLCOLLECTDIR'}, "building");
	if ($incremental) {
	    &gsprintf($out, "{buildcol.incremental_default_builddir}\n");
	}
    }
    $builddir =~ s/[\\\/]+/\//g;
    $builddir =~ s/\/$//;

    # update the archive cache if needed
    if ($cachedir) {
	&gsprintf($out, "{buildcol.updating_archive_cache}\n")
	    if ($verbosity >= 1);

	$cachedir =~ s/[\\\/]+$//;
	$cachedir .= "/collect/$collection" unless 
	    $cachedir =~ /collect\/$collection/;

	$realarchivedir = "$cachedir/archives";
	$realbuilddir = "$cachedir/building";
	&util::mk_all_dir ($realarchivedir);
	&util::mk_all_dir ($realbuilddir);
	&util::cachedir ($archivedir, $realarchivedir, $verbosity);

    } else {
	$realarchivedir = $archivedir;
	$realbuilddir = $builddir;
    }

    # build it in realbuilddir
    &util::mk_all_dir ($realbuilddir);

    my ($buildertype, $builderdir,  $builder);
    # if a builder class has been created for this collection, use it
    # otherwise, use the mg or mgpp builder
    if (-e "$ENV{'GSDLCOLLECTDIR'}/custom/${collection}/perllib/custombuilder.pm") {
	$builderdir = "$ENV{'GSDLCOLLECTDIR'}/custom/${collection}/perllib";
	$buildertype = "custombuilder";
    } elsif (-e "$ENV{'GSDLCOLLECTDIR'}/perllib/custombuilder.pm") {
	$builderdir = "$ENV{'GSDLCOLLECTDIR'}/perllib";
	$buildertype = "custombuilder";
    } elsif (-e "$ENV{'GSDLCOLLECTDIR'}/perllib/${collection}builder.pm") {
	$builderdir = "$ENV{'GSDLCOLLECTDIR'}/perllib";
	$buildertype = "${collection}builder";
    } else {
	$builderdir = "$ENV{'GSDLHOME'}/perllib";
	if ($buildtype eq "lucene") {
	    $buildertype = "lucenebuilder";
	}
	elsif ($buildtype eq "lucene-emeraldview") {
	    $buildertype = "emeraldviewbuilder";
	}
	elsif ($buildtype eq "solr") {
	    $buildertype = "solrbuilder";
	}
	elsif ($buildtype eq "mgpp") {
	    $buildertype = "mgppbuilder";
	}
	else {
	    $buildertype = "mgbuilder";
	}
    }
	
    require "$builderdir/$buildertype.pm";

    eval("\$builder = new $buildertype(\$collection, " .
	 "\$realarchivedir, \$realbuilddir, \$verbosity, " .
	 "\$maxdocs, \$debug, \$keepold, \$incremental, \$incremental_mode, " .
	 "\$remove_empty_classifications, " .
	 "\$out, \$no_text, \$faillog, \$gli, \$disable_OAI)");
    die "$@" if $@;

    $builder->init();
    $builder->set_maxnumeric($maxnumeric);
    
    if (($buildertype eq "mgppbuilder") && $no_strip_html) {
	$builder->set_strip_html(0);
    }
    if ($sections_index_document_metadata ne "never") {
	$builder->set_sections_index_document_metadata($sections_index_document_metadata);
    }
        
    if ($mode =~ /^all$/i) {
	$builder->compress_text($textindex);
	$builder->build_indexes($indexname);
	$builder->make_infodatabase();
	$builder->collect_specific();
    } elsif ($mode =~ /^compress_text$/i) {
	$builder->compress_text($textindex);
    } elsif ($mode =~ /^build_index$/i) {
	$builder->build_indexes($indexname);	
    } elsif ($mode =~ /^infodb$/i) {
	$builder->make_infodatabase();
    } else {
	(&gsprintf(STDERR, "{buildcol.unknown_mode}\n", $mode) && die);
    }

    $builder->make_auxiliary_files() if !$debug;
    $builder->deinit();
    
    if (($realbuilddir ne $builddir) && !$debug) {
	&gsprintf($out, "{buildcol.copying_back_cached_build}\n")
	    if ($verbosity >= 1);
	&util::rm_r ($builddir);
	&util::cp_r ($realbuilddir, $builddir);
    }

    close OUT if $close_out;
    close FAILLOG;

    print STDERR "</Build>\n" if $gli;
}
## main() ##


