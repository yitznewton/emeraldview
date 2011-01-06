###########################################################################
#
# List.pm -- A general and flexible list classifier with most of
#                   the abilities of AZCompactList, and better Unicode,
#                   metadata and sorting capabilities.
#
# A component of the Greenstone digital library software
# from the New Zealand Digital Library Project at the
# University of Waikato, New Zealand.
#
# Author: Michael Dewsnip, NZDL Project, University of Waikato, NZ
#
# Copyright (C) 2005 New Zealand Digital Library Project
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
# TO DO: - Remove punctuation from metadata values before sorting.
#        - Add an AZCompactList-style hlist option?
#
###########################################################################

package List;


use BaseClassifier;

use strict;


sub BEGIN {
    @List::ISA = ('BaseClassifier');
}

my $partition_type_list =
    [ { 'name' => "per_letter",
	'desc' => "{List.level_partition.per_letter}" },
      { 'name' => "approximate_size",
	'desc' => "{List.level_partition.approximate_size}"},
      { 'name' => "constant_size",
	'desc' => "{List.level_partition.constant_size}" },
      { 'name' => "none",
	'desc' => "{List.level_partition.none}" } ];

# following used to check types later on
my $valid_partition_types = { 'per_letter' => 1,
			      'constant_size' => 1,
			      'per_letter_fixed_size' => 1,
			      'approximate_size' => 1,
			      'none' => 1};

my $bookshelf_type_list =
    [ { 'name' => "always",
	'desc' => "{List.bookshelf_type.always}" },
      { 'name' => "duplicate_only",
	'desc' => "{List.bookshelf_type.duplicate_only}" },
      { 'name' => "never",
	'desc' => "{List.bookshelf_type.never}" } ];

my $arguments =
    [ { 'name' => "metadata",
	'desc' => "{List.metadata}",
	'type' => "metadata",
	'reqd' => "yes" },

      # The interesting options
      { 'name' => "bookshelf_type",
	'desc' => "{List.bookshelf_type}",
	'type' => "enum",
	'list' => $bookshelf_type_list,
	'deft' => "never" },
      { 'name' => "classify_sections",
	'desc' => "{List.classify_sections}",
	'type' => "flag" },
      { 'name' => "partition_type_within_level",
	'desc' => "{List.partition_type_within_level}",
	'type' => "enumstring",  # Must be enumstring because multiple values can be specified (separated by '/')
	'list' => $partition_type_list,
	'deft' => "per_letter" },
      { 'name' => "partition_size_within_level",
	'desc' => "{List.partition_size_within_level}",
	'type' => "string" },  # Must be string because multiple values can be specified (separated by '/')
      { 'name' => "partition_name_length",
	'desc' => "{List.partition_name_length}",
	'type' => "string" },
      { 'name' => "sort_leaf_nodes_using",
	'desc' => "{List.sort_leaf_nodes_using}",
	'type' => "metadata",
	'deft' => "Title" },
      { 'name' => "sort_using_unicode_collation",
	'desc' => "{List.sort_using_unicode_collation}",
	'type' => "flag" },
      { 'name' => "use_hlist_for",
	'desc' => "{List.use_hlist_for}",
	'type' => "string" },
      { 'name' => "removeprefix",
	'desc' => "{BasClas.removeprefix}",
	'type' => "regexp" },
      { 'name' => "removesuffix",
	'desc' => "{BasClas.removesuffix}",
	'type' => "regexp" } ];

my $options = { 'name'     => "List",
		'desc'     => "{List.desc}",
		'abstract' => "no",
		'inherits' => "yes",
		'args'     => $arguments };


sub new
{
    my ($class) = shift(@_);
    my ($classifierslist, $inputargs, $hashArgOptLists) = @_;
    push(@$classifierslist, $class);

    push(@{$hashArgOptLists->{"ArgList"}}, @{$arguments});
    push(@{$hashArgOptLists->{"OptList"}}, $options);

    my $self = new BaseClassifier($classifierslist, $inputargs, $hashArgOptLists);

    if ($self->{'info_only'}) {
	# don't worry about any options etc
	return bless $self, $class;
    }

    # The metadata elements to use (required)
    if (!$self->{'metadata'}) {
	die "Error: No metadata fields specified for List.\n";
    }
    my @metadata_groups = split(/\//, $self->{'metadata'});
    $self->{'metadata_groups'} = \@metadata_groups;

    # The classifier button name (default: the first metadata element specified)
    if (!$self->{'buttonname'}) {
	my $first_metadata_group = $metadata_groups[0];
	my $first_metadata_element = (split(/\;|,/, $first_metadata_group))[0];
	$self->{'buttonname'} = $self->generate_title_from_metadata($first_metadata_element);
    }

    # Whether to group items into a bookshelf, (must be 'always' for all metadata fields except the last)
    foreach my $metadata_group (@metadata_groups) {
	$self->{$metadata_group . ".bookshelf_type"} = "always";
    }
    my $last_metadata_group = $metadata_groups[$#metadata_groups];
    # Default: duplicate_only, ie. leave leaf nodes ungrouped (equivalent to AZCompactList -mingroup 2)
    $self->{$last_metadata_group . ".bookshelf_type"} = $self->{'bookshelf_type'};

    # Whether to use an hlist or a vlist for each level in the hierarchy (default: vlist)
    foreach my $metadata_group (@metadata_groups) {
	$self->{$metadata_group . ".list_type"} = "VList";
    }
    foreach my $metadata_group (split(/\,/, $self->{'use_hlist_for'})) {
	$self->{$metadata_group . ".list_type"} = "HList";
    }

    # How the items are grouped into partitions (default: no partition)
    # for each level (metadata group), separated by '/'
    if (!$self->{"partition_type_within_level"}) {
	foreach my $metadata_group (@metadata_groups) {
	    $self->{$metadata_group . ".partition_type_within_level"} = "none";
	}
    } else {
	my @partition_type_within_levellist = split(/\//, $self->{'partition_type_within_level'});

	my $first = 1;
	foreach my $metadata_group (@metadata_groups) {
	    my $partition_type_within_levelelem = shift(@partition_type_within_levellist);
	    if (defined($partition_type_within_levelelem) && $partition_type_within_levelelem eq "per_letter_fixed_size") {
		print STDERR "per letter fixed size, changing to approximate size\n";
		$partition_type_within_levelelem = "approximate_size";
	    }
	    if (defined($partition_type_within_levelelem) && defined $valid_partition_types->{$partition_type_within_levelelem}) {
		$self->{$metadata_group . ".partition_type_within_level"} = $partition_type_within_levelelem;
	    }
	    else {
		if ($first) {
		    $self->{$metadata_group . ".partition_type_within_level"} = "none";
		    $first = 0;
		} else {
		    $self->{$metadata_group . ".partition_type_within_level"} = $self->{$metadata_groups[0] . ".partition_type_within_level"};
		}
		if (defined($partition_type_within_levelelem)) {
		    # ie invalid entry
		    print STDERR "invalid partition type for level $metadata_group: $partition_type_within_levelelem, defaulting to ". $self->{$metadata_group . ".partition_type_within_level"} ."\n";
		}
	    }
	}
    }

    # The number of items in each partition
    if (!$self->{'partition_size_within_level'}) {
	# Default: 20
	foreach my $metadata_group (@metadata_groups) {
	    $self->{$metadata_group . ".partition_size_within_level"} = 20;
	}
    }
    else {
	my @partition_size_within_levellist = split(/\//, $self->{'partition_size_within_level'});

	# Assign values based on the partition_size_within_level parameter
	foreach my $metadata_group (@metadata_groups) {
	    my $partition_size_within_levelelem = shift(@partition_size_within_levellist);
	    if (defined($partition_size_within_levelelem)) {
		$self->{$metadata_group . ".partition_size_within_level"} = $partition_size_within_levelelem;
	    }
	    else {
		$self->{$metadata_group . ".partition_size_within_level"} = $self->{$metadata_groups[0] . ".partition_size_within_level"};
	    }
	}
    }

    # The removeprefix and removesuffix expressions
    if ($self->{'removeprefix'}) {
	# If there are more than one expressions, use '' to quote each experession and '/' to separate
	my @removeprefix_exprs_within_levellist = split(/'\/'/, $self->{'removeprefix'});

	foreach my $metadata_group (@metadata_groups) {
	    my $removeprefix_expr_within_levelelem = shift(@removeprefix_exprs_within_levellist);
	    if (defined($removeprefix_expr_within_levelelem) && $removeprefix_expr_within_levelelem ne "") {
		# Remove the other ' at the beginning and the end if there is any
		$removeprefix_expr_within_levelelem =~ s/^'//;
		$removeprefix_expr_within_levelelem =~ s/'$//;
		# Remove the extra ^ at the beginning
		$removeprefix_expr_within_levelelem =~ s/^\^//;
		$self->{$metadata_group . ".remove_prefix_expr"} = $removeprefix_expr_within_levelelem;
	    } else {
		$self->{$metadata_group . ".remove_prefix_expr"} = $self->{$metadata_groups[0] . ".remove_prefix_expr"};
	    }
	}
    }
    if ($self->{'removesuffix'}) {
	my @removesuffix_exprs_within_levellist = split(/'\/'/, $self->{'removesuffix'});

	foreach my $metadata_group (@metadata_groups) {
	    my $removesuffix_expr_within_levelelem = shift(@removesuffix_exprs_within_levellist);
	    if (defined($removesuffix_expr_within_levelelem) && $removesuffix_expr_within_levelelem ne "") {
		$removesuffix_expr_within_levelelem =~ s/^'//;
		$removesuffix_expr_within_levelelem =~ s/'$//;
		# Remove the extra $ at the end
		$removesuffix_expr_within_levelelem =~ s/\$$//;
		$self->{$metadata_group . ".remove_suffix_expr"} = $removesuffix_expr_within_levelelem;
	    } else {
		$self->{$metadata_group . ".remove_suffix_expr"} = $self->{$metadata_groups[0] . ".remove_suffix_expr"};
	    }
	}
    }

    # The metadata elements to use to sort the leaf nodes (default: Title)
    my @sort_leaf_nodes_using_metadata_groups = ( "Title" );
    if ($self->{'sort_leaf_nodes_using'}) {
	@sort_leaf_nodes_using_metadata_groups = split(/\|/, $self->{'sort_leaf_nodes_using'});
    }
    $self->{'sort_leaf_nodes_using_metadata_groups'} = \@sort_leaf_nodes_using_metadata_groups;

    # Create an instance of the Unicode::Collate object if better Unicode sorting is desired
    if ($self->{'sort_using_unicode_collation'}) {
	# To use this you first need to download the allkeys.txt file from
        # http://www.unicode.org/Public/UCA/latest/allkeys.txt and put it in the Perl
        # Unicode/Collate directory.
	require Unicode::Collate;
	$self->{'unicode_collator'} = Unicode::Collate->new();
    }

    # An empty array for the document OIDs
    $self->{'OIDs'} = [];

    return bless $self, $class;
}


sub init
{
    # Nothing to do...
}


# Called for each document in the collection
sub classify
{
    my $self = shift(@_);
    my ($doc_obj,$edit_mode) = @_;

    # If "-classify_sections" is set, classify every section of the document
    if ($self->{'classify_sections'}) {
	my $section = $doc_obj->get_next_section($doc_obj->get_top_section());
	while (defined $section) {
	    $self->classify_section($doc_obj, $doc_obj->get_OID() . ".$section", $section, $edit_mode);
	    $section = $doc_obj->get_next_section($section);
	}
    }
    # Otherwise just classify the top document section
    else {
	$self->classify_section($doc_obj, $doc_obj->get_OID(), $doc_obj->get_top_section(), $edit_mode);
    }
}


sub classify_section
{
    my $self = shift(@_);
    my ($doc_obj,$section_OID,$section,$edit_mode) = @_;

    my @metadata_groups = @{$self->{'metadata_groups'}};

    # Only classify the section if it has a value for one of the metadata elements in the first group
    my $classify_section = 0;
    my $first_metadata_group = $metadata_groups[0];
    foreach my $first_metadata_group_element (split(/\;|,/, $first_metadata_group)) {
	my $real_first_metadata_group_element = $self->strip_ex_from_metadata($first_metadata_group_element);
	my $first_metadata_group_element_value = $doc_obj->get_metadata_element($section, $real_first_metadata_group_element);

	# Remove prefix/suffix if requested
	my $remove_prefix_expr = $self->{$first_metadata_group_element . ".remove_prefix_expr"};
	if (defined $remove_prefix_expr && $remove_prefix_expr ne "") {
	    $first_metadata_group_element_value =~ s/^$remove_prefix_expr//;
	}
	my $remove_suffix_expr = $self->{$first_metadata_group_element . ".remove_suffix_expr"};
	if (defined $remove_suffix_expr && $remove_suffix_expr ne "") {
	    $first_metadata_group_element_value =~ s/$remove_suffix_expr$//;
	}

	if (defined($first_metadata_group_element_value) && $first_metadata_group_element_value ne "") {
	    # This section must be included in the classifier
	    $classify_section = 1;
	    last;
	}
    }

    # We're not classifying this section because it doesn't have the required metadata
    return if (!$classify_section);

    if (($edit_mode eq "delete") || ($edit_mode eq "update")) {
	$self->oid_array_delete($section_OID,'OIDs');
	if ($edit_mode eq "delete") {
	    return;
	}
    }

    # Otherwise, include this section in the classifier
    push(@{$self->{'OIDs'}}, $section_OID);

    # Create a hash for the metadata values of each metadata element we're interested in
    my %metadata_groups_done = ();
    foreach my $metadata_group (@metadata_groups, @{$self->{'sort_leaf_nodes_using_metadata_groups'}}) {
	# Take care not to do a metadata group more than once
	unless ($metadata_groups_done{$metadata_group}) {
	    if ($edit_mode eq "update") {
		# if we are updating, we delete all the old values before
		# adding the new ones, otherwise, the section will end up in
		# the classifier twice.
		delete $self->{$metadata_group . ".list"}->{$section_OID};
	    }
	    foreach my $metadata_element (split(/\;|,/, $metadata_group)) {
		my $real_metadata_element = $self->strip_ex_from_metadata($metadata_element);

		my $remove_prefix_expr = $self->{$metadata_element . ".remove_prefix_expr"};
		my $remove_suffix_expr = $self->{$metadata_element . ".remove_suffix_expr"};
		my @metadata_values = @{$doc_obj->get_metadata($section, $real_metadata_element)};
		foreach my $metadata_value (@metadata_values) {
		    # Strip leading and trailing whitespace
		    $metadata_value =~ s/^\s*//;
		    $metadata_value =~ s/\s*$//;

		    # Remove prefix/suffix if requested
		    if (defined $remove_prefix_expr && $remove_prefix_expr ne "") {
			$metadata_value =~ s/^$remove_prefix_expr//;
		    }
		    if (defined $remove_suffix_expr && $remove_suffix_expr ne "") {
			$metadata_value =~ s/$remove_suffix_expr$//;
		    }

		    # uppercase the metadata - makes the AZList nicer
		    $metadata_value = uc($metadata_value);
		    # Convert the metadata value from a UTF-8 string to a Unicode string
		    # This means that length() and substr() work properly
		    # We need to be careful to convert classifier node title values back to UTF-8, however
		    my $metadata_value_unicode_string = $self->convert_utf8_string_to_unicode_string($metadata_value);

		    # Add the metadata value into the list for this combination of metadata group and section
		    push(@{$self->{$metadata_group . ".list"}->{$section_OID}}, $metadata_value_unicode_string);
		}
		last if (@metadata_values > 0);
	    }

	    $metadata_groups_done{$metadata_group} = 1;
	}
    }
}


sub get_classify_info
{
    my $self = shift(@_);

    # The metadata groups to classify by
    my @metadata_groups = @{$self->{'metadata_groups'}};
    my $first_metadata_group = $metadata_groups[0];

    # The OID values of the documents to include in the classifier
    my @OIDs = @{$self->{'OIDs'}};

    # Create the root node of the classification hierarchy
    my %classifier_node = ( 'thistype' => "Invisible",
			    'childtype' => $self->{$first_metadata_group . ".list_type"},
			    'Title' => $self->{'buttonname'},
			    'contains' => [],
			    'mdtype' => $first_metadata_group );

    # Recursively create the classification hierarchy, one level for each metadata group
    $self->add_level(\@metadata_groups, \@OIDs, \%classifier_node);
    return \%classifier_node;
}


sub add_level
{
    my $self = shift(@_);
    my @metadata_groups = @{shift(@_)};
    my @OIDs = @{shift(@_)};
    my $classifier_node = shift(@_);
    # print STDERR "\nAdding AZ list for " . $classifier_node->{'Title'} . "\n";

    my $metadata_group = $metadata_groups[0];
    # print STDERR "Processing metadata group: " . $metadata_group . "\n";
    # print STDERR "Number of OID values: " . @OIDs . "\n";

    if (!defined($self->{$metadata_group . ".list"})) {
	print STDERR "Warning: No metadata values assigned to $metadata_group.\n";
	return;
    }

    # Create a mapping from metadata value to OID
    my $OID_to_metadata_values_hash_ref = $self->{$metadata_group . ".list"};
    my %metadata_value_to_OIDs_hash = ();
    foreach my $OID (@OIDs)
    {
	if ($OID_to_metadata_values_hash_ref->{$OID})
	{
	    my @metadata_values = @{$OID_to_metadata_values_hash_ref->{$OID}};
	    foreach my $metadata_value (@metadata_values)
	    {
		push(@{$metadata_value_to_OIDs_hash{$metadata_value}}, $OID);
	    }
	}
    }
    # print STDERR "Number of distinct values: " . scalar(keys %metadata_value_to_OIDs_hash) . "\n";

    # Partition the values (if necessary)
    my $partition_type_within_level = $self->{$metadata_group . ".partition_type_within_level"};
    my $partition_size_within_level = $self->{$metadata_group . ".partition_size_within_level"};
    if ($partition_type_within_level =~ /^per_letter$/i) {
	# Generate one hlist for each letter
	my @sortedmetadata_values = $self->sort_metadata_values_array(keys(%metadata_value_to_OIDs_hash));
	my %metadata_value_to_OIDs_subhash = ();

	my $lastpartition = substr($sortedmetadata_values[0], 0, 1);
	foreach my $metadata_value (@sortedmetadata_values) {
	    my $metadata_valuepartition = substr($metadata_value, 0, 1);

	    # Is this the start of a new partition?
	    if ($metadata_valuepartition ne $lastpartition) {
		$self->add_hlist_partition(\@metadata_groups, $classifier_node, $lastpartition, \%metadata_value_to_OIDs_subhash);
		%metadata_value_to_OIDs_subhash = ();
		$lastpartition = $metadata_valuepartition;
	    }

	    $metadata_value_to_OIDs_subhash{$metadata_value} = $metadata_value_to_OIDs_hash{$metadata_value};
	}

	# Don't forget to add the last partition
	$self->add_hlist_partition(\@metadata_groups, $classifier_node, $lastpartition, \%metadata_value_to_OIDs_subhash);

	# The partitions are stored in an HList
	$classifier_node->{'childtype'} = "HList";
    }
    elsif ($partition_type_within_level =~ /^approximate_size$/i && scalar(keys %metadata_value_to_OIDs_hash) > $partition_size_within_level) {
	# Generate hlist based on the first letter of the metadata value (like per_letter) but with restriction on the partition size
	# If a letter has fewer items than specified by the "partition_size_within_level", then group them together if possible
	# If a letter has more items than specified, split into several hlists.
	# Depends on the bookshelf_type, one item can be either a document (when bookshelf_type is "never") or a metadata value (otherwise)
	my $partition_size_within_level = $self->{$metadata_group . ".partition_size_within_level"};
	my @sortedmetadata_values = $self->sort_metadata_values_array(keys(%metadata_value_to_OIDs_hash));
	my $bookshelf_type = $self->{$metadata_group . ".bookshelf_type"};

	# Separate values by their first letter, each form a bucket, like the per_letter partition type
	my $last_partition = substr($sortedmetadata_values[0], 0, 1);
	my %partition_buckets = ();
	my @metadata_values_in_bucket = ();
	my $num_items_in_bucket = 0;
	foreach my $metadata_value (@sortedmetadata_values) {
	    my $metadata_valuepartition = substr($metadata_value, 0, 1);
	    if ($metadata_valuepartition ne $last_partition) {
		my @temp_array = @metadata_values_in_bucket;
		# Cache the values that belong to this bucket, and the number of items in this bucket, not necessary to be the same number as the metadata values
		my %partition_info = ();
		$partition_info{'metadata_values'} = \@temp_array;
		$partition_info{'size'} = $num_items_in_bucket;
		$partition_buckets{$last_partition} = \%partition_info;

		@metadata_values_in_bucket = ($metadata_value);
		$num_items_in_bucket = $bookshelf_type eq "never" ? scalar(@{$metadata_value_to_OIDs_hash{$metadata_value}}) : scalar(@metadata_values_in_bucket);
		$last_partition = $metadata_valuepartition;
	    } else {
		$num_items_in_bucket += $bookshelf_type eq "never" ? scalar(@{$metadata_value_to_OIDs_hash{$metadata_value}}) : scalar(@metadata_values_in_bucket);
		push (@metadata_values_in_bucket, $metadata_value);
	    }
	}
	# Last one
	my %partition_info = ();
	$partition_info{'metadata_values'} = \@metadata_values_in_bucket;
	$partition_info{'size'} = $num_items_in_bucket;
	$partition_buckets{$last_partition} = \%partition_info;

	my @partition_keys = $self->sort_metadata_values_array(keys(%partition_buckets));
	for (my $i = 0; $i < scalar(@partition_keys) - 1; $i++) {
	    my $partition = $partition_keys[$i];
	    my $items_in_partition = $partition_buckets{$partition}->{'size'};
	    # Merge small buckets together, but keep the numeric bucket apart
	    if ($items_in_partition < $partition_size_within_level) {
		my $items_in_next_partition = $partition_buckets{$partition_keys[$i+1]}->{'size'};
		if ($items_in_partition + $items_in_next_partition <= $partition_size_within_level
		    && !(($partition =~ /^[^0-9]/ && $partition_keys[$i+1] =~ /^[0-9]/)
			 || ($partition =~ /^[0-9]/ && $partition_keys[$i+1] =~ /^[^0-9]/))) {
		    foreach my $metadata_value_to_merge (@{$partition_buckets{$partition}->{'metadata_values'}}) {
			push(@{$partition_buckets{$partition_keys[$i+1]}->{'metadata_values'}}, $metadata_value_to_merge);
		    }
		    $partition_buckets{$partition_keys[$i+1]}->{'size'} += $items_in_partition;
		    delete $partition_buckets{$partition};
		}
	    }
	}
	@partition_keys = $self->sort_metadata_values_array(keys(%partition_buckets));

	# Add partitions, and divide big bucket into several
	my $last_partition_end = "";
	my $partition_start = "";
	foreach my $partition (@partition_keys) {
	    my @metadata_values = $self->sort_metadata_values_array(@{$partition_buckets{$partition}->{'metadata_values'}});
	    my $items_in_partition = $partition_buckets{$partition}->{'size'};
	    $partition_start = $self->generate_partition_start($metadata_values[0], $last_partition_end, $self->{"partition_name_length"});

	    if ($items_in_partition > $partition_size_within_level) {
		my $items_done = 0;
		my %metadata_values_to_OIDs_subhashes = ();
		for (my $i = 0; $i < scalar(@metadata_values); $i++) {
		    my $metadata_value = $metadata_values[$i];
		    # If the bookshelf_type is "never", count the documents, otherwise count the distinct metadata values
		    my $items_for_this_md_value = $bookshelf_type eq "never" ? scalar(@{$metadata_value_to_OIDs_hash{$metadata_value}}) : 1;

		    my $partitionend = $self->generate_partition_end($metadata_value, $partition_start, $self->{"partition_name_length"});
		    my $partitionname = $partition_start;
		    if ($partitionend ne $partition_start) {
			$partitionname = $partitionname . "-" . $partitionend;
		    }

		    # Start a new partition
		    if ($items_done + $items_for_this_md_value > $partition_size_within_level && $items_done != 0) {
			$self->add_hlist_partition(\@metadata_groups, $classifier_node, $partitionname, \%metadata_values_to_OIDs_subhashes);
			$last_partition_end = $partitionend;
			$partition_start = $self->generate_partition_start($metadata_value, $last_partition_end, $self->{"partition_name_length"});
			$items_done = 0;
			%metadata_values_to_OIDs_subhashes = ();
 		    }

		    # If bookshelf_type is "never" and the current metadata value holds too many items, need to split into several partitions
                    if ($bookshelf_type eq "never" && $items_for_this_md_value > $partition_size_within_level) {
			my $partitionname_for_this_value = $self->generate_partition_start($metadata_value, $last_partition_end, $self->{"partition_name_length"});
			# Get the number of partitions needed for this value
			my $num_splits = int($items_for_this_md_value / $partition_size_within_level);
			$num_splits++ if ($items_for_this_md_value / $partition_size_within_level > $num_splits);

			my @OIDs_for_this_value = @{$metadata_value_to_OIDs_hash{$metadata_value}};
			for (my $i = 0; $i < $num_splits; $i++) {
			    my %OIDs_subhashes_for_this_value = ();
			    my @OIDs_for_this_partition = ();
			    for (my $d = $i * $partition_size_within_level; $d < (($i+1) * $partition_size_within_level > $items_for_this_md_value ? $items_for_this_md_value : ($i+1) * $partition_size_within_level); $d++) {
				push (@OIDs_for_this_partition, $OIDs_for_this_value[$d]);
			    }

			    # The last bucket might have only a few items and need to be merged with buckets for subsequent metadata values
			    if ($i == $num_splits - 1 && scalar(@OIDs_for_this_partition) < $partition_size_within_level) {
				$metadata_values_to_OIDs_subhashes{$metadata_value} = \@OIDs_for_this_partition;
				$items_done += scalar(@OIDs_for_this_partition);
				next;
			    }

			    # Add an HList for this bucket
			    $OIDs_subhashes_for_this_value{$metadata_value} = \@OIDs_for_this_partition;
			    $self->add_hlist_partition(\@metadata_groups, $classifier_node, $partitionname_for_this_value, \%OIDs_subhashes_for_this_value);
			    $last_partition_end = $partitionname_for_this_value;
			}
			next;
                    }

		    $metadata_values_to_OIDs_subhashes{$metadata_value} = $metadata_value_to_OIDs_hash{$metadata_value};
		    $items_done += $bookshelf_type eq "never" ? scalar(@{$metadata_values_to_OIDs_subhashes{$metadata_value}}) : 1;

		    # The last partition
		    if($i == scalar(@metadata_values) - 1) {
			$self->add_hlist_partition(\@metadata_groups, $classifier_node, $partitionname, \%metadata_values_to_OIDs_subhashes);
		    }
		}
	    }
	    else {
		# The easier case, just add a partition
		my %metadata_values_to_OIDs_subhashes = ();
		for (my $i = 0; $i < scalar(@metadata_values); $i++) {
		    my $metadata_value = $metadata_values[$i];
		    $metadata_values_to_OIDs_subhashes{$metadata_value} = $metadata_value_to_OIDs_hash{$metadata_value};
		}
		my $last_metadata_value = $metadata_values[scalar(@metadata_values)-1];
		my $partitionend = $self->generate_partition_end($last_metadata_value, $partition_start, $self->{"partition_name_length"});
		my $partitionname = $partition_start;
		if ($partitionend ne $partition_start) {
		    $partitionname = $partitionname . "-" . $partitionend;
		}
		$self->add_hlist_partition(\@metadata_groups, $classifier_node, $partitionname, \%metadata_values_to_OIDs_subhashes);
		$last_partition_end = $partitionend;
	    }
	}

	# The partitions are stored in an HList
	$classifier_node->{'childtype'} = "HList";

    } # end approximate_size
    else {
	# Generate hlists of a certain size
	if ($partition_type_within_level =~ /^constant_size$/i && scalar(keys %metadata_value_to_OIDs_hash) > $partition_size_within_level) {
	    my @sortedmetadata_values = $self->sort_metadata_values_array(keys(%metadata_value_to_OIDs_hash));
	    my $itemsdone = 0;
	    my %metadata_value_to_OIDs_subhash = ();
	    my $lastpartitionend = "";
	    my $partitionstart;
	    foreach my $metadata_value (@sortedmetadata_values) {
		$metadata_value_to_OIDs_subhash{$metadata_value} = $metadata_value_to_OIDs_hash{$metadata_value};
		$itemsdone++;
		my $itemsinpartition = scalar(keys %metadata_value_to_OIDs_subhash);

		# Is this the start of a new partition?
		if ($itemsinpartition == 1) {
		    $partitionstart = $self->generate_partition_start($metadata_value, $lastpartitionend, $self->{"partition_name_length"});
		}

		# Is this the end of the partition?
		if ($itemsinpartition == $partition_size_within_level || $itemsdone == @sortedmetadata_values) {
		    my $partitionend = $self->generate_partition_end($metadata_value, $partitionstart, $self->{"partition_name_length"});
		    my $partitionname = $partitionstart;
		    if ($partitionend ne $partitionstart) {
			$partitionname = $partitionname . "-" . $partitionend;
		    }

		    $self->add_hlist_partition(\@metadata_groups, $classifier_node, $partitionname, \%metadata_value_to_OIDs_subhash);
		    %metadata_value_to_OIDs_subhash = ();
		    $lastpartitionend = $partitionend;
		}
	    }

	    # The partitions are stored in an HList
	    $classifier_node->{'childtype'} = "HList";
	}

	# Otherwise just add all the values to a VList
	else {
	    $self->add_vlist(\@metadata_groups, $classifier_node, \%metadata_value_to_OIDs_hash);
	}
    }
}


sub convert_utf8_string_to_unicode_string
{
    my $self = shift(@_);
    my $utf8_string = shift(@_);

    my $unicode_string = "";
    foreach my $unicode_value (@{&unicode::utf82unicode($utf8_string)}) {
	$unicode_string .= chr($unicode_value);
    }
    return $unicode_string;
}


sub convert_unicode_string_to_utf8_string
{
    my $self = shift(@_);
    my $unicode_string = shift(@_);

    my @unicode_array;
    for (my $i = 0; $i < length($unicode_string); $i++) {
	push(@unicode_array, ord(substr($unicode_string, $i, 1)));
    }
    return &unicode::unicode2utf8(\@unicode_array);
}


sub generate_partition_start
{
    my $self = shift(@_);
    my $metadata_value = shift(@_);
    my $lastpartitionend = shift(@_);
    my $partition_name_length = shift(@_);

    if ($partition_name_length) {
	return substr($metadata_value, 0, $partition_name_length);
    }

    my $partitionstart = substr($metadata_value, 0, 1);
    if ($partitionstart le $lastpartitionend) {
	$partitionstart = substr($metadata_value, 0, 2);
	# Give up after three characters
	if ($partitionstart le $lastpartitionend) {
	    $partitionstart = substr($metadata_value, 0, 3);
	}
    }

    return $partitionstart;
}


sub generate_partition_end
{
    my $self = shift(@_);
    my $metadata_value = shift(@_);
    my $partitionstart = shift(@_);
    my $partition_name_length = shift(@_);

    if ($partition_name_length) {
	return substr($metadata_value, 0, $partition_name_length);
    }

    my $partitionend = substr($metadata_value, 0, length($partitionstart));
    if ($partitionend gt $partitionstart) {
	$partitionend = substr($metadata_value, 0, 1);
	if ($partitionend le $partitionstart) {
	    $partitionend = substr($metadata_value, 0, 2);
	    # Give up after three characters
	    if ($partitionend le $partitionstart) {
		$partitionend = substr($metadata_value, 0, 3);
	    }
	}
    }

    return $partitionend;
}


sub add_hlist_partition
{
    my $self = shift(@_);
    my @metadata_groups = @{shift(@_)};
    my $classifier_node = shift(@_);
    my $partitionname = shift(@_);
    my $metadata_value_to_OIDs_hash_ref = shift(@_);

    # Create an hlist partition
    my %child_classifier_node = ( 'Title' => $self->convert_unicode_string_to_utf8_string($partitionname),
				  'childtype' => "VList",
				  'contains' => [] );

    # Add the children to the hlist partition
    $self->add_vlist(\@metadata_groups, \%child_classifier_node, $metadata_value_to_OIDs_hash_ref);
    push(@{$classifier_node->{'contains'}}, \%child_classifier_node);
}


sub add_vlist
{
    my $self = shift(@_);
    my @metadata_groups = @{shift(@_)};
    my $classifier_node = shift(@_);
    my $metadata_value_to_OIDs_hash_ref = shift(@_);

    my $metadata_group = shift(@metadata_groups);
    $classifier_node->{'mdtype'} = $metadata_group;

    # Create an entry in the vlist for each value
    foreach my $metadata_value ($self->sort_metadata_values_array(keys(%{$metadata_value_to_OIDs_hash_ref})))
    {
	my @OIDs = @{$metadata_value_to_OIDs_hash_ref->{$metadata_value}};

	# If there is only one item and 'bookshelf_type' is not always (ie. never or duplicate_only), add the item to the list
	if (@OIDs == 1 && $self->{$metadata_group . ".bookshelf_type"} ne "always") {
	    my $OID = $OIDs[0];
	    my $offset = $self->metadata_offset($metadata_group, $OID, $metadata_value);
	    push(@{$classifier_node->{'contains'}}, { 'OID' => $OID, 'offset' => $offset });
	}
	# If 'bookshelf_type' is 'never', list all the items even if there are duplicated values
	elsif ($self->{$metadata_group . ".bookshelf_type"} eq "never") {
	    @OIDs = $self->sort_leaf_items(\@OIDs);
	    foreach my $OID (@OIDs) {
		my $offset = $self->metadata_offset($metadata_group, $OID, $metadata_value);
		push(@{$classifier_node->{'contains'}}, { 'OID' => $OID , 'offset' => $offset });
	    }

	}
	# Otherwise create a sublist (bookshelf) for the metadata value
	else {
	    my %child_classifier_node = ( 'Title' => $self->convert_unicode_string_to_utf8_string($metadata_value),
					  'childtype' => "VList",
					  'mdtype' => $metadata_group,
					  'contains' => [] );

	    # If there are metadata elements remaining, recursively apply the process
	    if (@metadata_groups > 0) {
		my $next_metadata_group = $metadata_groups[0];
		$child_classifier_node{'childtype'} = $self->{$next_metadata_group . ".list_type"};
		$self->add_level(\@metadata_groups, \@OIDs, \%child_classifier_node);
	    }
	    # Otherwise just add the documents as children of this list
	    else {
		@OIDs = $self->sort_leaf_items(\@OIDs);
		foreach my $OID (@OIDs) {
		    my $offset = $self->metadata_offset($metadata_group, $OID, $metadata_value);
		    push(@{$child_classifier_node{'contains'}}, { 'OID' => $OID , 'offset' => $offset });
		}

	    }

	    # Add the sublist to the list
	    push(@{$classifier_node->{'contains'}}, \%child_classifier_node);
	}
    }
}

sub metadata_offset
{
    my $self = shift(@_);
    my $metadata_group = shift(@_);
    my $OID = shift(@_);
    my $metadata_value = shift(@_);

    my $OID_to_metadata_values_hash_ref = $self->{$metadata_group . ".list"};
    my @metadata_values = @{$OID_to_metadata_values_hash_ref->{$OID}};
    for (my $i = 0; $i < scalar(@metadata_values); $i++) {
	if ($metadata_value eq $metadata_values[$i]) {
	    return $i;
	}
    }

    return 0;
}

sub sort_leaf_items
{
    my $self = shift(@_);
    my @OIDs = @{shift(@_)};
#    my $classifier_node = shift(@_);

    # Sort leaf nodes and add to list
    my @sort_leaf_nodes_using_metadata_groups = @{$self->{'sort_leaf_nodes_using_metadata_groups'}};
    foreach my $sort_leaf_nodes_usingmetaelem (reverse @sort_leaf_nodes_using_metadata_groups) {
	my $OID_to_metadata_values_hash_ref = $self->{$sort_leaf_nodes_usingmetaelem . ".list"};
	# Force a stable sort (Perl 5.6's sort isn't stable)
	# !! The [0] bits aren't ideal (multiple metadata values) !!
	@OIDs = @OIDs[ sort { $OID_to_metadata_values_hash_ref->{$OIDs[$a]}[0] cmp $OID_to_metadata_values_hash_ref->{$OIDs[$b]}[0] || $a <=> $b; } 0..$#OIDs ];
    }
    return @OIDs;
}



sub sort_metadata_values_array
{
    my $self = shift(@_);
    my @metadata_values = @_;

    if ($self->{'unicode_collator'}) {
	return $self->{'unicode_collator'}->sort(@metadata_values);
    }
    else {
	return sort(@metadata_values);
    }
}


1;
