<?php

$finder = (new PhpCsFixer\Finder())
	->in([
		__DIR__.'/src',
	])
;

return (new PhpCsFixer\Config())
	->setRiskyAllowed(true)
	->setRules([
		'@Symfony' => true,
		'@Symfony:risky' => true,
		'@PHP84Migration' => true,
		'align_multiline_comment' => [
			'comment_type' => 'all_multiline',
		],
		'array_syntax' => ['syntax' => 'short'],
		'attribute_empty_parentheses' => [
			'use_parentheses' => false,
		],
		'braces_position' => [
			'classes_opening_brace' => 'same_line',
			'functions_opening_brace' => 'same_line',
		],
		'combine_consecutive_issets' => true,
		'combine_consecutive_unsets' => true,
		'echo_tag_syntax' => [
			'format' => 'short',
		],
		'fopen_flags' => false,
		'heredoc_to_nowdoc' => true,
		'list_syntax' => ['syntax' => 'short'],
		'multiline_comment_opening_closing' => true,
		'multiline_whitespace_before_semicolons' => [
			'strategy' => 'new_line_for_chained_calls',
		],
		'no_superfluous_elseif' => true,
		'no_useless_else' => true,
		'no_useless_return' => true,
		'no_useless_sprintf' => true,
		'return_assignment' => true,
		'simplified_if_return' => true,
		'simplified_null_return' => true,
		'string_implicit_backslashes' => true,
		'string_line_ending' => true,
		'void_return' => true,
		'yoda_style' => [
			'equal' => false,
			'identical' => false,
			'less_and_greater' => false,
		],
	])
	->setFinder($finder)
	->setIndent("\t")
	->setLineEnding("\n");
